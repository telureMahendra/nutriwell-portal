<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<style>
.notification-detail-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 25px;
}

.detail-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.detail-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.detail-label {
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
}

.detail-value {
    color: #333;
    font-size: 15px;
}

.audience-badge {
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
}

.badge-all { background: #e3f2fd; color: #1976d2; }
.badge-paid { background: #e8f5e8; color: #2e7d32; }
.badge-free { background: #fff3e0; color: #ef6c00; }
.badge-specific { background: #fce4ec; color: #c2185b; }

.client-list {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #eee;
    border-radius: 5px;
    padding: 10px;
    background: #f9f9f9;
}

.client-item {
    padding: 8px;
    border-bottom: 1px solid #eee;
}

.client-item:last-child {
    border-bottom: none;
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="notification-detail-card">
            <div class="row">
                <div class="col-md-8">
                    <h3 style="margin-top: 0; color: #333;">Notification Details</h3>
                </div>
                <div class="col-md-4 text-right">
                    <a href="<?= base_url('notifications') ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="detail-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Notification ID</div>
                        <div class="detail-value">#<?= $notification->id ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Created On</div>
                        <div class="detail-value"><?= date('F j, Y \a\t g:i A', strtotime($notification->created_at)) ?></div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-label">Title</div>
                <div class="detail-value" style="font-size: 18px; font-weight: 500;">
                    <?= esc($notification->title) ?>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-label">Message</div>
                <div class="detail-value" style="background: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;">
                    <?= esc($notification->message) ?>
                </div>
            </div>

            <?php if ($notification->image_url): ?>
            <div class="detail-section">
                <div class="detail-label">Image</div>
                <div class="detail-value">
                    <img src="<?= base_url($notification->image_url) ?>" 
                         alt="Notification Image" 
                         style="max-width: 300px; max-height: 200px; border-radius: 5px; border: 1px solid #ddd;">
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-section">
                <div class="detail-label">Audience Type</div>
                <div class="detail-value">
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
                </div>
            </div>

            <?php if (($audienceType == '3') && !empty($notification->client_ids)): ?>
            <div class="detail-section">
                <div class="detail-label">Selected Clients</div>
                <div class="detail-value">
                    <?php
                    $clientIds = json_decode($notification->client_ids, true);
                    if (!empty($clientIds)) {
                        echo '<div class="client-list">';
                        
                        // Instead of using $this->common_model in the view, we need to pass the client data from controller
                        if (isset($selectedClients) && !empty($selectedClients)) {
                            // If client data is passed from controller
                            foreach ($selectedClients as $client) {
                                echo '<div class="client-item">';
                                echo '<strong>' . esc($client->client_name) . '</strong>';
                                echo '<br><small class="text-muted">' . esc($client->client_mobile_number) . '</small>';
                                if (!empty($client->client_email)) {
                                    echo '<br><small class="text-muted">' . esc($client->client_email) . '</small>';
                                }
                                echo '</div>';
                            }
                        } else {
                            // Fallback: just show client IDs if client data is not available
                            foreach ($clientIds as $clientId) {
                                echo '<div class="client-item">';
                                echo '<strong>Client ID: ' . $clientId . '</strong>';
                                echo '<br><small class="text-muted">Details not available</small>';
                                echo '</div>';
                            }
                        }
                        echo '</div>';
                        echo '<div class="mt-2"><small class="text-muted">Total: ' . count($clientIds) . ' clients</small></div>';
                    } else {
                        echo '<div class="text-muted">No specific clients selected</div>';
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?= $this->endSection(); ?>