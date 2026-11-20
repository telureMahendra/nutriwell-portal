<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;

/**
 * Admin chat contacts + message audit logger.
 *
 * The admin may chat with ANY client. UI is rendered by the floating widget
 * injected in layouts/admin_default.php; this controller only exposes JSON
 * endpoints the widget calls.
 */
class Chat extends Controller
{
    protected $session;
    protected $db;

    public function __construct()
    {
        helper('common_functions');
        $this->session = session();
        $this->db = \Config\Database::connect();
    }

    /** GET /admin/chat/contacts — list of every client the admin can chat with. */
    public function contacts()
    {
        if (!is_admin_logged_in()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'msg' => 'Not admin']);
        }

        $search = trim((string) $this->request->getGet('q'));
        $builder = $this->db->table('client_profile AS C')
            ->select("C.client_id, C.client_name, C.client_email, C.client_mobile_number, C.status")
            ->where('C.status !=', 'Deleted')
            ->orderBy('C.client_name', 'ASC')
            ->limit(500);

        if ($search !== '') {
            $builder->groupStart()
                ->like('C.client_name', $search)
                ->orLike('C.client_email', $search)
                ->orLike('C.client_mobile_number', $search)
                ->groupEnd();
        }

        $rows = $builder->get()->getResult();
        return $this->response->setJSON(['status' => true, 'data' => $rows]);
    }

    /** POST /admin/chat/log — record a message + fire FCM push. */
    public function log()
    {
        if (!is_admin_logged_in()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false]);
        }
        $resp = _chat_log_message($this->request, $this->session, 'admin', (int) $this->session->get('userid'));
        _chat_fire_push($this->request, 'admin', $this->session->get('fullname') ?: 'Admin');
        return $resp;
    }

    /**
     * GET /admin/chat/firebase-token — returns a custom token with role claims
     * so the widget can signInWithCustomToken() and satisfy Firestore rules.
     */
    public function firebaseToken()
    {
        if (!is_admin_logged_in()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false]);
        }
        return _chat_issue_firebase_token('admin:' . (int) $this->session->get('userid'), [
            'role'    => 'admin',
            'userId'  => (int) $this->session->get('userid'),
        ]);
    }

    /** GET /admin/chat/history?client_id=123 — the portal-side audit copy. */
    public function history()
    {
        if (!is_admin_logged_in()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false]);
        }
        $clientId = (int) $this->request->getGet('client_id');
        if ($clientId <= 0) {
            return $this->response->setJSON(['status' => false, 'msg' => 'client_id required']);
        }
        $rows = $this->db->table('chat_messages')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'ASC')
            ->limit(500)
            ->get()->getResult();
        return $this->response->setJSON(['status' => true, 'data' => $rows]);
    }
}

/**
 * Shared log helper — used by both Admin\Chat::log and Mentor\Chat::log.
 * Kept in this file to avoid a separate helper file for two callers.
 */
if (!function_exists('_chat_log_message')) {
    function _chat_log_message($request, $session, string $senderType, int $senderId)
    {
        $clientId  = (int) $request->getPost('client_id');
        $message   = (string) $request->getPost('message');
        $type      = (string) $request->getPost('message_type'); // text/image/pdf/file
        $fileUrl   = (string) $request->getPost('file_url');
        $fileDesc  = (string) $request->getPost('file_desc');
        $category  = strtolower((string) $request->getPost('category'));
        $restricted= (int) $request->getPost('restricted');

        if (!in_array($type, ['text', 'image', 'pdf', 'file'], true)) $type = 'text';
        if (!in_array($category, ['general', 'diet_plan', 'workout_plan', 'prescription'], true)) {
            $category = 'general';
        }
        if ($clientId <= 0) {
            return service('response')->setJSON(['status' => false, 'msg' => 'client_id required']);
        }

        $db = \Config\Database::connect();
        $db->table('chat_messages')->insert([
            'conversation_id' => (string) $clientId,
            'client_id'       => $clientId,
            'sender_type'     => $senderType,
            'sender_id'       => $senderId,
            'sender_name'     => $session->get('fullname') ?: $senderType,
            'message'         => $message,
            'message_type'    => $type,
            'file_url'        => $fileUrl ?: null,
            'file_desc'       => $fileDesc ?: null,
            'category'        => $category,
            'restricted'      => $restricted ? 1 : 0,
        ]);

        return service('response')->setJSON([
            'status' => true,
            'id'     => $db->insertID(),
        ]);
    }
}

if (!function_exists('_chat_issue_firebase_token')) {
    /**
     * Build a Firebase Auth custom token for the current portal user.
     * Returns 503 gracefully if the service account isn't configured yet —
     * widget falls back to open-rules mode.
     */
    function _chat_issue_firebase_token(string $uid, array $claims)
    {
        if (!\App\Libraries\FirebaseAdmin::isConfigured()) {
            return service('response')->setStatusCode(503)->setJSON([
                'status' => false,
                'msg'    => 'Firebase service account not configured on server.',
            ]);
        }
        try {
            $admin = new \App\Libraries\FirebaseAdmin();
            $tok = $admin->createCustomToken($uid, $claims);
            return service('response')->setJSON(['status' => true, 'token' => $tok]);
        } catch (\Throwable $e) {
            return service('response')->setStatusCode(500)->setJSON([
                'status' => false,
                'msg'    => $e->getMessage(),
            ]);
        }
    }
}

if (!function_exists('_chat_fire_push')) {
    /**
     * After a portal-side message is logged, send an FCM push to the target
     * client's device using the FCM token stored in the database.
     */
    function _chat_fire_push($request, string $senderRole, string $senderName)
    {
        try {
            $clientId = (int) $request->getPost('client_id');
            if ($clientId <= 0) return;

            $text = (string) $request->getPost('message');
            $type = (string) $request->getPost('message_type');
            $body = $text !== '' ? $text : (
                $type === 'image' ? '📷 Photo' :
                ($type === 'pdf' || $type === 'file' ? '📄 Document' : 'New message')
            );
            if (strlen($body) > 120) $body = substr($body, 0, 117) . '...';

            $db = \Config\Database::connect();
            $device = $db->table('client_login')
                ->select('fcm_token')
                ->where('client_id', $clientId)
                ->where('client_status', 'Active')
                ->where('fcm_token !=', '')
                ->where('fcm_token IS NOT NULL')
                ->get()
                ->getRow();

            if (!$device || empty($device->fcm_token)) return;
            $deviceToken = $device->fcm_token;

            // Use HTTP v1 FirebaseAdmin if configured
            if (\App\Libraries\FirebaseAdmin::isConfigured()) {
                $admin = new \App\Libraries\FirebaseAdmin();
                $admin->sendFcm($deviceToken, $senderName, $body, [
                    'type'              => 'chat',
                    'client_id'         => (string) $clientId,
                    'from_role'         => $senderRole,
                    'notification_type' => '13',
                ]);
            } else {
                // Fallback to legacy sendNotification if FirebaseAdmin JSON isn't present
                helper('common_functions');
                sendNotification(
                    $clientId,
                    $senderName,
                    $body,
                    [$deviceToken]
                );
            }
        } catch (\Throwable $e) {
            log_message('warning', '_chat_fire_push: ' . $e->getMessage());
        }
    }
}
