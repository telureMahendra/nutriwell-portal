<?php
namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\Notification_model;

class Notifications extends Controller
{
    protected $session;
    protected $validation;
    protected $common_model;
    protected $notification_model;

    public function __construct()
    {
        $this->validation        = Services::validation();
        $this->common_model      = model('App\Models\Common_model', false);
        $this->notification_model= model('App\Models\Notification_model', false);
        $this->session           = session();

        if (!is_admin_logged_in()) {
            return redirect()->to('/');
        }

        $controller = class_basename(service('router')->controllerName());
        $methodName = class_basename(service('router')->methodName());
        hasAccess($controller, $methodName);
    }

    // ── List ──────────────────────────────────────────────────────────────
    public function index()
    {
        if (!$this->session->has('userid')) return view('admin/index');

        $data = [
            'menutitle'        => 'Notifications',
            'pagetitle'        => 'Manage Notifications',
            'breadcrumb'       => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li><li>Notifications</li></ul>',
            'notificationList' => $this->getNotificationsWithAudience(),
        ];
        return view('admin/notifications/list', $data);
    }

    // ── Create form ───────────────────────────────────────────────────────
    public function create()
    {
        if (!$this->session->has('userid')) return redirect()->to('/');

        $data = [
            'menutitle' => 'Notifications',
            'pagetitle' => 'Create Notification',
            'breadcrumb'=> '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li><li>Create Notification</li></ul>',
            'clients'   => $this->common_model->getAllData('client_profile', ['status' => 'Active']),
        ];
        return view('admin/notifications/create', $data);
    }

    // ── Store (save + optionally fire FCM) ────────────────────────────────
    public function store()
    {
        if (!$this->session->has('userid')) return redirect()->to('/');

        $validation = \Config\Services::validation();
        $validation->setRules([
            'title'             => 'required|min_length[3]|max_length[255]',
            'description'       => 'required|min_length[5]',
            'audience_type'     => 'required|in_list[0,1,2,3]',
            'schedule_type'     => 'required|in_list[now,schedule]',
            'client_ids'        => 'permit_empty',
            'schedule_datetime' => 'permit_empty|valid_datetime',
            'image_file'        => 'permit_empty|uploaded[image_file]|max_size[image_file,2048]|is_image[image_file]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $msg = 'Please fix the following errors:<br>';
            foreach ($validation->getErrors() as $field => $error) {
                $msg .= '• ' . ucfirst(str_replace('_', ' ', $field)) . ': ' . $error . '<br>';
            }
            $this->session->setFlashdata('error', $msg);
            return redirect()->back()->withInput();
        }

        // Image upload
        $image_url = null;
        $file = $this->request->getFile('image_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $name = $file->getRandomName();
            $file->move('uploads/notifications', $name);
            $image_url = 'uploads/notifications/' . $name;
        }

        // Schedule
        $schedule_type     = $this->request->getPost('schedule_type');
        $schedule_datetime = $this->request->getPost('schedule_datetime');
        $send_now          = ($schedule_type !== 'schedule' || empty($schedule_datetime));
        $scheduled_at      = $send_now ? date('Y-m-d H:i:s') : $schedule_datetime;
        $status            = $send_now ? 'pending' : 'scheduled';

        // Save notification
        $notificationId = $this->common_model->addData('notifications', [
            'title'        => trim($this->request->getPost('title')),
            'message'      => trim($this->request->getPost('description')),
            'image_url'    => $image_url,
            'deep_link'    => null,
            'type'         => 'info',
            'created_by'   => $this->session->get('userid'),
            'scheduled_at' => $scheduled_at,
            'sent_at'      => null,
            'status'       => $status,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        if (!$notificationId) {
            $this->session->setFlashdata('error', 'Failed to create notification. Please try again.');
            return redirect()->back()->withInput();
        }

        // Save audience
        $audience_type = $this->request->getPost('audience_type');
        $client_ids    = $this->request->getPost('client_ids');

        $this->common_model->addData('notification_audience', [
            'notification_id' => $notificationId,
            'audience_type'   => $audience_type,
            'client_ids'      => ($audience_type == '3' && !empty($client_ids)) ? json_encode($client_ids) : null,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        // Fire FCM immediately if "Send Now"
        if ($send_now) {
            $sentCount = $this->dispatchFcm(
                $notificationId,
                $this->request->getPost('title'),
                $this->request->getPost('description'),
                $audience_type,
                $client_ids,
                $image_url
            );

            $db = \Config\Database::connect();
            $db->table('notifications')->where('id', $notificationId)->update([
                'status'  => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
            ]);

            $this->session->setFlashdata('success', "Notification created and sent to {$sentCount} device(s).");
        } else {
            $this->session->setFlashdata('success', 'Notification scheduled successfully!');
        }

        return redirect()->to('notifications');
    }

    // ── Manual send (re-send) ─────────────────────────────────────────────
    public function send($id)
    {
        if (!$this->session->has('userid')) return redirect()->to('/');

        $notification = $this->getNotificationWithAudience($id);
        if (!$notification) {
            $this->session->setFlashdata('error', 'Notification not found.');
            return redirect()->to('notifications');
        }

        $clientIds = $notification->client_ids ? json_decode($notification->client_ids, true) : null;
        $sentCount = $this->dispatchFcm(
            $id,
            $notification->title,
            $notification->message,
            $notification->audience_type,
            $clientIds,
            $notification->image_url
        );

        $db = \Config\Database::connect();
        $db->table('notifications')->where('id', $id)->update([
            'status'  => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->setFlashdata('success', "Sent to {$sentCount} device(s).");
        return redirect()->to('notifications');
    }

    // ── View ──────────────────────────────────────────────────────────────
    public function view($id)
    {
        if (!$this->session->has('userid')) return redirect()->to('/');

        $notification = $this->getNotificationWithAudience($id);
        if (empty($notification)) {
            throw PageNotFoundException::forPageNotFound("Notification not found");
        }

        $data = [
            'menutitle'    => 'Notifications',
            'pagetitle'    => 'View Notification',
            'breadcrumb'   => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li><li>View Notification</li></ul>',
            'notification' => $notification,
        ];
        return view('admin/notifications/view', $data);
    }

    // ── Delete ────────────────────────────────────────────────────────────
    public function delete($id)
    {
        if (!$this->session->has('userid')) return redirect()->to('/');
        if (empty($id) || !is_numeric($id)) {
            $this->session->setFlashdata('error', 'Invalid notification ID.');
            return redirect()->to(base_url('notifications'));
        }

        $db   = \Config\Database::connect();
        $row  = $db->table('notifications')->where('id', $id)->get()->getRow();
        if (!$row) {
            $this->session->setFlashdata('error', 'Notification not found.');
            return redirect()->to(base_url('notifications'));
        }

        $db->transStart();
        $db->table('notification_audience')->where('notification_id', $id)->delete();
        $db->table('notifications')->where('id', $id)->delete();
        $db->transComplete();

        $msg = $db->transStatus() ? 'Notification deleted successfully!' : 'Database error while deleting.';
        $key = $db->transStatus() ? 'success' : 'error';
        $this->session->setFlashdata($key, $msg);
        return redirect()->to(base_url('notifications'));
    }

    // ── AJAX client search ────────────────────────────────────────────────
    public function searchClients()
    {
        $q = $this->request->getGet('q');
        if (empty($q)) return $this->response->setJSON(['clients' => []]);

        $db      = \Config\Database::connect();
        $clients = $db->table('client_profile')
            ->select('client_id, client_name, client_mobile_number, client_email')
            ->groupStart()
                ->like('client_name', $q)
                ->orLike('client_mobile_number', $q)
                ->orLike('client_email', $q)
            ->groupEnd()
            ->where('status', 'Active')
            ->limit(20)
            ->get()->getResult();

        return $this->response->setJSON(['clients' => $clients]);
    }

    // ── Private: fire FCM to target audience ──────────────────────────────
    private function dispatchFcm(int $notificationId, string $title, string $body, $audience_type, $client_ids = null, ?string $image_url = null): int
    {
        if (!\App\Libraries\FirebaseAdmin::isConfigured()) {
            log_message('warning', 'Notifications: Firebase not configured, skipping FCM dispatch.');
            return 0;
        }

        $targetIds = $this->getTargetClientIds($audience_type, $client_ids);
        if (empty($targetIds)) {
            log_message('info', "Notifications #{$notificationId}: no target clients for audience_type={$audience_type}");
            return 0;
        }

        $db      = \Config\Database::connect();
        $devices = $db->table('client_login')
            ->select('fcm_token, client_id as user_id')
            ->whereIn('client_id', $targetIds)
            ->where('client_status', 'Active')
            ->where('fcm_token !=', '')
            ->where('fcm_token IS NOT NULL')
            ->get()->getResult();

        if (empty($devices)) {
            log_message('info', "Notifications #{$notificationId}: no active FCM tokens found.");
            return 0;
        }

        try {
            $firebase = new \App\Libraries\FirebaseAdmin();
        } catch (\Throwable $e) {
            log_message('error', 'Notifications: FirebaseAdmin init failed: ' . $e->getMessage());
            return 0;
        }

        $extraData = [
            'notification_id' => (string) $notificationId,
            'type'            => 'info',
        ];
        if ($image_url) {
            $extraData['image_url'] = base_url($image_url);
        }

        $sent = 0;
        foreach ($devices as $device) {
            $ok = $firebase->sendFcm($device->fcm_token, $title, $body, $extraData);
            if ($ok) {
                $sent++;
            } else {
                // Clear bad token
                $db->table('client_login')
                   ->where('client_id', $device->user_id)
                   ->where('fcm_token', $device->fcm_token)
                   ->update(['fcm_token' => '']);
            }
        }

        log_message('info', "Notifications #{$notificationId}: FCM sent to {$sent}/" . count($devices) . " devices.");
        return $sent;
    }

    // ── Private: resolve audience_type → array of client_ids ─────────────
    private function getTargetClientIds($audience_type, $client_ids = null): array
    {
        $db = \Config\Database::connect();

        switch ((string) $audience_type) {
            case '0': // All active clients
                $rows = $db->table('client_profile')
                    ->select('client_id')
                    ->where('status', 'Active')
                    ->get()->getResult();
                return array_column($rows, 'client_id');

            case '1': // Paid — have at least one active booking
                $rows = $db->table('client_profile AS CP')
                    ->select('CP.client_id')
                    ->join('booking AS B', 'B.client_id = CP.client_id AND B.status = "Active"', 'inner')
                    ->where('CP.status', 'Active')
                    ->get()->getResult();
                return array_values(array_unique(array_column($rows, 'client_id')));

            case '2': // Free — no active booking
                $rows = $db->table('client_profile AS CP')
                    ->select('CP.client_id')
                    ->where('CP.status', 'Active')
                    ->where("CP.client_id NOT IN (SELECT client_id FROM booking WHERE status = 'Active')", null, false)
                    ->get()->getResult();
                return array_column($rows, 'client_id');

            case '3': // Specific clients
                if (empty($client_ids)) return [];
                $ids = is_string($client_ids) ? json_decode($client_ids, true) : (array) $client_ids;
                return array_values(array_filter(array_map('intval', $ids)));

            default:
                return [];
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────
    private function getNotificationsWithAudience(): array
    {
        $db = \Config\Database::connect();
        return $db->table('notifications n')
            ->select('n.*, na.audience_type, na.client_ids, na.created_at as audience_created')
            ->join('notification_audience na', 'n.id = na.notification_id', 'left')
            ->orderBy('n.id', 'DESC')
            ->get()->getResult();
    }

    private function getNotificationWithAudience($id)
    {
        $db = \Config\Database::connect();
        return $db->table('notifications n')
            ->select('n.*, na.audience_type, na.client_ids, na.created_at as audience_created')
            ->join('notification_audience na', 'n.id = na.notification_id', 'left')
            ->where('n.id', $id)
            ->get()->getRow();
    }
}
