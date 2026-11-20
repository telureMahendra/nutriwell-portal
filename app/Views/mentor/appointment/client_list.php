<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>

<style>
    .client-stats {
        font-size: 0.85em;
        color: #666;
    }
    .stats-badge {
        display: inline-block;
        padding: 2px 8px;
        margin: 0 2px;
        border-radius: 10px;
        font-size: 0.75em;
    }
    .badge-completed { background: #d4edda; color: #155724; }
    .badge-upcoming { background: #cce5ff; color: #004085; }
    .badge-cancelled { background: #f8d7da; color: #721c24; }
    .last-appointment {
        font-size: 0.85em;
        color: #666;
    }
</style>

<div class="content">
    <div class="page-bar"> <?= $breadcrumb; ?> </div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <?= $pagetitle ?>
                            </div>
                            <div class="tools">
                                <a href="javascript:;" class="collapse"></a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="table-toolbar">
                                <?php $attributes = array('method'=>'get','class' => 'login-form', 'id' => 'searchform');
                                echo form_open('mentor/clients', $attributes); ?>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="search" placeholder="Search by name, email or phone" value="<?= esc($search); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <select class="form-control" name="status">
                                                <option value="all" <?= ($status == 'all') ? 'selected' : ''; ?>>All Status</option>
                                                <option value="active" <?= ($status == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?= ($status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2" align="left">
                                        <div class="form-group">
                                            <div>
                                                <input type="submit" name="submit" value="Filter" class="btn green" />
                                                <a href="<?= base_url('mentor/clients'); ?>" class="btn btn-wht">Reset</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?= form_close(); ?>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-modern" id="sample_1">
                                    <thead>
                                        <tr>
                                            <th width="5%">Sr. No.</th>
                                            <th width="20%">Client Name</th>
                                            <th width="15%">Contact Info</th>
                                            <th width="15%">Health Info</th>
                                            <th width="20%">Appointment Statistics</th>
                                            <th width="15%">Last Appointment</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($clientList) > 0) {
                                            $k = 1 + ($_GET['page'] ?? 0) * PAGELIMIT;
                                            foreach ($clientList as $row) {
                                        ?>
                                                <tr class="odd gradeX">
                                                    <td><?= $k; ?></td>
                                                    <td>
                                                        <strong><?= esc($row->client_name); ?></strong><br>
                                                        <small class="text-muted">ID: <?= $row->client_id; ?></small>
                                                    </td>
                                                    <td>
                                                        <i class="fa fa-phone"></i> <?= esc($row->client_mobile_number); ?><br>
                                                        <i class="fa fa-envelope"></i> <?= esc($row->client_email); ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($row->age): ?>
                                                            Age: <?= esc($row->age); ?> yrs<br>
                                                        <?php endif; ?>
                                                        <?php if ($row->weight): ?>
                                                            Weight: <?= esc($row->weight); ?> kg<br>
                                                        <?php endif; ?>
                                                        <?php if ($row->height): ?>
                                                            Height: <?= esc($row->height); ?> <?= esc($row->height_in); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($row->appointment_stats): ?>
                                                            Total: <strong><?= $row->appointment_stats->total_appointments; ?></strong><br>
                                                            <div class="client-stats">
                                                                <span class="stats-badge badge-completed">Completed: <?= $row->appointment_stats->completed; ?></span>
                                                                <span class="stats-badge badge-upcoming">Upcoming: <?= $row->appointment_stats->upcoming; ?></span>
                                                                <?php if(isset($row->appointment_stats->missed) && $row->appointment_stats->missed > 0): ?>
                                                                    <span class="stats-badge" style="background: #fdf5e6; color: #d35400;">Missed: <?= $row->appointment_stats->missed; ?></span>
                                                                <?php endif; ?>
                                                                <span class="stats-badge badge-cancelled">Cancelled: <?= $row->appointment_stats->cancelled; ?></span>
                                                            </div>
                                                        <?php else: ?>
                                                            No appointments yet
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($row->last_appointment): ?>
                                                            <div class="last-appointment">
                                                                <?= date('d/m/Y', strtotime($row->last_appointment->date)); ?><br>
                                                                <?php 
                                                                    $app_status = $row->last_appointment->status;
                                                                    if ($app_status == 'Upcoming' && strtotime($row->last_appointment->date) < strtotime('today')) {
                                                                        $app_status = 'Missed';
                                                                    }
                                                                ?>
                                                                <span class="label label-<?= ($app_status == 'Completed') ? 'success' : (($app_status == 'Upcoming') ? 'warning' : (($app_status == 'Missed') ? 'default' : 'danger')); ?>">
                                                                    <?= $app_status; ?>
                                                                </span>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">No appointments</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= base_url('mentor/clients/view/' . $row->client_id); ?>" class="btn default btn-xs blue" title="View Client" alt="View Client">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="<?= base_url('mentor/appointment/clientdashboard/' . $row->client_id); ?>" class="btn default btn-xs green" title="Client Dashboard" alt="Client Dashboard">
                                                            <i class="fa fa-dashboard"></i>
                                                        </a>
                                                        <a href="<?= base_url('mentor/appointment?search=' . urlencode($row->client_name)); ?>" class="btn default btn-xs yellow" title="View Appointments" alt="View Appointments">
                                                            <i class="fa fa-calendar"></i>
                                                        </a>
                                                        <button type="button" class="btn default btn-xs purple"
                                                                title="Chat with Client"
                                                                onclick="window.openChatWith && window.openChatWith(<?= (int) $row->client_id ?>)">
                                                            <i class="fa fa-comments"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                        <?php $k++;
                                            }
                                        } else {
                                        ?>
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <div class="alert alert-info">
                                                        No clients found. Clients will appear here once they have appointments with you.
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if (isset($pager) && $pager): ?>
                                <div class="row">
                                    <div class="col-md-6" style="text-align:center;">
                                        <?php echo $pager->links(); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo $pager->simpleLinks(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Add any client-side functionality here
        $('#searchform').on('submit', function() {
            // You can add validation here
            return true;
        });
    });
</script>

<?= $this->endSection(); ?>