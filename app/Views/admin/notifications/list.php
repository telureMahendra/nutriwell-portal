<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<style>
.notification-table {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
}

.table-actions {
    display: flex;
    gap: 8px;
}

.table-modern {
    width: 100%;
    border-collapse: collapse !important;
}

.btn-action {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-view {
    background: #28a745;
    color: white;
}

.btn-edit {
    background: #ffc107;
    color: black;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.audience-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.green-meadow.btn {
    color: #FFFFFF !important;
    background-color: #f57f27 !important;
}


.badge-all { background: #e3f2fd; color: #1976d2; }
.badge-paid { background: #e8f5e8; color: #2e7d32; }
.badge-free { background: #fff3e0; color: #ef6c00; }
.badge-specific { background: #fce4ec; color: #c2185b; }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption font-dark">
                    <i class="icon-bell font-dark"></i>
                    <span class="caption-subject bold uppercase">Manage Notifications</span>
                </div>
                <div class="actions">
                    <a href="<?= base_url('notifications/create') ?>" class="btn btn-circle green-meadow">
                        <i class="fa fa-plus"></i> Create Notification
                    </a>
                </div>
            </div>
            
            <div class="portlet-body">
                <!-- Success/Error Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="fa fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="fa fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <div class="notification-table">
                    <table class="table table-striped table-modern" id="sample_1">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Audience</th>
                                <th>Status</th>
                                <th>Created On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($notificationList)): ?>
                                <?php foreach ($notificationList as $notification): ?>
                                    <tr>
                                        <td><?= $notification->id ?></td>
                                        <td>
                                            <strong><?= esc($notification->title) ?></strong>
                                            <?php if ($notification->image_url): ?>
                                                <br><small class="text-muted">Has Image</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= character_limiter($notification->message, 100) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $audienceType = $notification->audience_type ?? '0';
                                            $badgeClass = [
                                                '0' => 'badge-all',
                                                '1' => 'badge-paid', 
                                                '2' => 'badge-free',
                                                '3' => 'badge-specific'
                                            ][$audienceType] ?? 'badge-all';
                                            
                                            $audienceName = [
                                                '0' => 'All Clients',
                                                '1' => 'Paid Clients',
                                                '2' => 'Free Clients', 
                                                '3' => 'Specific Clients'
                                            ][$audienceType] ?? 'Unknown';
                                            ?>
                                            <span class="audience-badge <?= $badgeClass ?>">
                                                <?= $audienceName ?>
                                            </span>
                                            <?php if ($audienceType == '3' && $notification->client_ids): ?>
                                                <br><small class="text-muted"><?= count(json_decode($notification->client_ids ?? '[]')) ?> clients</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $st = $notification->status ?? 'pending';
                                            $stMap = [
                                                'sent'       => ['label-success', 'fa-check-circle',  'Sent'],
                                                'scheduled'  => ['label-warning', 'fa-clock-o',       'Scheduled'],
                                                'pending'    => ['label-default', 'fa-hourglass-half','Pending'],
                                                'processing' => ['label-info',    'fa-spinner',        'Processing'],
                                                'failed'     => ['label-danger',  'fa-times-circle',  'Failed'],
                                            ];
                                            [$lc, $ic, $lt] = $stMap[$st] ?? ['label-default', 'fa-question', ucfirst($st)];
                                            ?>
                                            <span class="label <?= $lc ?>" style="font-size:11px;padding:4px 8px;">
                                                <i class="fa <?= $ic ?>"></i> <?= $lt ?>
                                            </span>
                                            <?php if ($notification->sent_at): ?>
                                                <br><small class="text-muted"><?= date('M j g:i A', strtotime($notification->sent_at)) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= date('M j, Y g:i A', strtotime($notification->created_at)) ?>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="<?= base_url('notifications/view/' . $notification->id) ?>"
                                                   class="btn-action btn-view" title="View">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                                <?php if (in_array($notification->status ?? 'pending', ['pending', 'scheduled', 'failed'])): ?>
                                                <a href="<?= base_url('notifications/send/' . $notification->id) ?>"
                                                   class="btn-action" style="background:#2196f3;color:#fff;"
                                                   onclick="return confirm('Send this notification via FCM now?')" title="Send Now">
                                                    <i class="fa fa-paper-plane"></i> Send
                                                </a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('notifications/delete/' . $notification->id) ?>"
                                                   class="btn-action btn-delete"
                                                   onclick="return confirm('Are you sure you want to delete this notification?')"
                                                   title="Delete">
                                                    <i class="fa fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fa fa-bell-slash fa-2x"></i><br>
                                        No notifications found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>