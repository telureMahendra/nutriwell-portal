<?php

namespace App\Controllers\Mentor;

use CodeIgniter\Controller;

/**
 * Mentor chat contacts + message audit logger.
 *
 * The mentor may ONLY chat with clients associated through the `appointment`
 * table (same rule used by Mentor\Client::index).
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

    /**
     * Returns all unique client IDs linked to this doctor via appointment
     * (non-cancelled sessions) OR via booking (mentor assigned, no session yet).
     */
    private function getAssignedClientIds(int $doctorId): array
    {
        $ids = [];

        $appt = $this->db->table('appointment')
            ->select('client_id')
            ->where('doctor_id', $doctorId)
            ->where('status !=', 'Cancelled')
            ->get()->getResultArray();
        foreach ($appt as $r) {
            if (!empty($r['client_id'])) $ids[(int)$r['client_id']] = (int)$r['client_id'];
        }

        $book = $this->db->table('booking')
            ->select('client_id')
            ->where('doctor_id', $doctorId)
            ->where('status', 'Active')
            ->get()->getResultArray();
        foreach ($book as $r) {
            if (!empty($r['client_id'])) $ids[(int)$r['client_id']] = (int)$r['client_id'];
        }

        return array_values($ids);
    }

    /** GET /mentor/chat/contacts — only associated clients. */
    public function contacts()
    {
        if (!is_doctor_logged_in()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'msg' => 'Not mentor']);
        }
        $doctorId  = (int) $this->session->get('doctor_id');
        $clientIds = $this->getAssignedClientIds($doctorId);

        if (empty($clientIds)) {
            return $this->response->setJSON(['status' => true, 'data' => []]);
        }

        $search = trim((string) $this->request->getGet('q'));
        $builder = $this->db->table('client_profile AS C')
            ->select("C.client_id, C.client_name, C.client_email, C.client_mobile_number, C.status")
            ->whereIn('C.client_id', $clientIds)
            ->where('C.status !=', 'Deleted')
            ->orderBy('C.client_name', 'ASC');

        if ($search !== '') {
            $builder->groupStart()
                ->like('C.client_name', $search)
                ->orLike('C.client_email', $search)
                ->orLike('C.client_mobile_number', $search)
                ->groupEnd();
        }

        return $this->response->setJSON([
            'status' => true,
            'data'   => $builder->get()->getResult(),
        ]);
    }

    /** POST /mentor/chat/log — record a message in MySQL audit table. */
    public function log()
    {
        if (!is_doctor_logged_in()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false]);
        }
        $doctorId = (int) $this->session->get('doctor_id');
        $clientId = (int) $this->request->getPost('client_id');

        if (!in_array($clientId, $this->getAssignedClientIds($doctorId), true)) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'msg' => 'Not your client']);
        }

        $resp = _chat_log_message($this->request, $this->session, 'mentor', $doctorId);
        _chat_fire_push($this->request, 'mentor', $this->session->get('fullname') ?: ($this->session->get('username') ?: 'Mentor'));
        return $resp;
    }

    /**
     * GET /mentor/chat/firebase-token — returns a custom token with role claims
     * + the list of clients this mentor is associated with, so Firestore rules
     * can restrict writes to those specific chat docs.
     */
    public function firebaseToken()
    {
        if (!is_doctor_logged_in()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false]);
        }
        $doctorId  = (int) $this->session->get('doctor_id');
        $clientIds = array_map('strval', $this->getAssignedClientIds($doctorId));
        return _chat_issue_firebase_token('mentor:' . $doctorId, [
            'role'     => 'mentor',
            'userId'   => $doctorId,
            'clients'  => array_slice($clientIds, 0, 500),  // auth token size limit
        ]);
    }

    /** GET /mentor/chat/history?client_id=X */
    public function history()
    {
        if (!is_doctor_logged_in()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false]);
        }
        $doctorId = (int) $this->session->get('doctor_id');
        $clientId = (int) $this->request->getGet('client_id');
        if ($clientId <= 0) {
            return $this->response->setJSON(['status' => false, 'msg' => 'client_id required']);
        }
        if (!in_array($clientId, $this->getAssignedClientIds($doctorId), true)) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'msg' => 'Not your client']);
        }

        $rows = $this->db->table('chat_messages')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'ASC')
            ->limit(500)
            ->get()->getResult();
        return $this->response->setJSON(['status' => true, 'data' => $rows]);
    }
}

// The _chat_log_message helper is declared in Admin\Chat.php and auto-loaded
// when either controller is resolved. If Mentor\Chat is hit first we need a
// fallback — define it here guarded by function_exists.
if (!function_exists('_chat_log_message')) {
    function _chat_log_message($request, $session, string $senderType, int $senderId)
    {
        $clientId  = (int) $request->getPost('client_id');
        $message   = (string) $request->getPost('message');
        $type      = (string) $request->getPost('message_type');
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
            'sender_name'     => $session->get('fullname') ?: $session->get('username') ?: $senderType,
            'message'         => $message,
            'message_type'    => $type,
            'file_url'        => $fileUrl ?: null,
            'file_desc'       => $fileDesc ?: null,
            'category'        => $category,
            'restricted'      => $restricted ? 1 : 0,
        ]);

        return service('response')->setJSON(['status' => true, 'id' => $db->insertID()]);
    }
}

if (!function_exists('_chat_issue_firebase_token')) {
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
            return service('response')->setJSON([
                'status' => true,
                'token'  => $admin->createCustomToken($uid, $claims),
            ]);
        } catch (\Throwable $e) {
            return service('response')->setStatusCode(500)->setJSON([
                'status' => false,
                'msg'    => $e->getMessage(),
            ]);
        }
    }
}

if (!function_exists('_chat_fire_push')) {
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

            if (\App\Libraries\FirebaseAdmin::isConfigured()) {
                $admin = new \App\Libraries\FirebaseAdmin();
                $admin->sendFcm($deviceToken, $senderName, $body, [
                    'type'              => 'chat',
                    'client_id'         => (string) $clientId,
                    'from_role'         => $senderRole,
                    'notification_type' => '13',
                ]);
            } else {
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
