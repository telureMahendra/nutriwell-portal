<?php
/**
 * Floating chat widget — rendered once at the bottom of admin_default.php so
 * it appears on every admin/mentor page.
 *
 * This file decides:
 *   - which identity/role the current portal user has (admin vs mentor)
 *   - which backend endpoints the widget should hit
 *   - short-circuits entirely if no one is logged in
 */
$session   = session();
$usertype  = $session->get('usertype');
$role      = null;
$userId    = null;
$userName  = null;
$endpoints = [];

if ($usertype === 'admin') {
    $role     = 'admin';
    $userId   = (int) $session->get('userid');
    $userName = $session->get('fullname') ?: 'Admin';
    $endpoints = [
        'contacts'      => base_url('admin/chat/contacts'),
        'log'           => base_url('admin/chat/log'),
        'upload'        => base_url('chat/uploadFile'),
        'firebaseToken' => base_url('admin/chat/firebase-token'),
    ];
} elseif ($usertype === 'mentor') {
    $role     = 'mentor';
    $userId   = (int) $session->get('doctor_id');
    $userName = $session->get('fullname') ?: $session->get('username') ?: 'Mentor';
    $endpoints = [
        'contacts'      => base_url('mentor/chat/contacts'),
        'log'           => base_url('mentor/chat/log'),
        'upload'        => base_url('chat/uploadFile'),
        'firebaseToken' => base_url('mentor/chat/firebase-token'),
    ];
}

if ($role === null) {
    // Not logged in as admin or mentor — do not render widget.
    return;
}
?>
<link href="<?= base_url('public/assets/chat/chat-widget.css') ?>" rel="stylesheet" />

<!-- Firebase (compat build — matches the JS in chat-widget.js) -->
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-firestore-compat.js"></script>

<!-- Local Firebase project config (paste keys inside this file once) -->
<script src="<?= base_url('public/assets/chat/firebase-config.js') ?>"></script>

<script>
window.H4U_CHAT_USER = {
    role:      <?= json_encode($role) ?>,
    id:        <?= json_encode($userId) ?>,
    name:      <?= json_encode($userName) ?>,
    endpoints: <?= json_encode($endpoints) ?>,
};
</script>
<script src="<?= base_url('public/assets/chat/chat-widget.js') ?>"></script>
