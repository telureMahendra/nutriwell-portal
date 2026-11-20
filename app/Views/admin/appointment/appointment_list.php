<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation = \Config\Services::validation(); ?>

<style>
    .pagination {
        margin: 20px 0;
        float: left;
    }

    .pager {
        float: right;
    }

    .portlet.box.grey-cascade {
        border-radius: 10px !important;
        overflow: hidden !important;
        border: 1px solid #ddd;
    }

    .top-filter-row {
        background: #fff;
        border-bottom: 1px solid #E5E5E5;
        padding: 12px 15px;
        border-radius: 10px 10px 0 0 !important;
        margin-bottom: 15px;
    }

    .bt-add-appoin {
        color: #F57F27 !important;
        background-color: #f57f274a !important;
        border-color: #dbb233 !important;
    }

    .no-data-row {
        text-align: center;
        padding: 20px;
        background-color: #f9f9f9;
    }

    .debug-info {
        background: #f0f0f0;
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        font-size: 12px;
    }

    table.table-modern th {
        background-color: #f5f5f5;
        font-weight: 600;
    }

    .action-buttons a {
        margin-right: 5px;
    }
</style>

<div class="content">

    <!-- Page Bar -->
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li>
                <i class="fa fa-home"></i>
                <a href="<?= base_url(); ?>">Home</a>
                <i class="fa fa-angle-right"></i>
            </li>
            <li>
                <span><?= $pagetitle ?></span>
            </li>
        </ul>
    </div>

    <!-- Debug Info (Remove after testing) -->
    <!-- <div class="debug-info">
        <strong>Debug Info:</strong><br>
        Total Appointments: <?= !empty($appointmentList) ? count($appointmentList) : 0 ?><br>
        Client ID from session: <?= session()->get('userid') ?? 'Not set' ?><br>
        Date Range: <?= $fromdate ?? 'N/A' ?> to <?= $todate ?? 'N/A' ?><br>
        Search Term: "<?= $search ?? '' ?>"
    </div> -->

    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">
                        <div class="portlet-body">

                            <!-- Filter Form -->
                            <?php
                            $attributes = ['method' => 'get', 'class' => 'login-form', 'id' => 'loginform'];
                            echo form_open('appointment', $attributes);
                            ?>
                            <!-- In your filter form in the view -->
<div class="row top-filter-row">
    <div class="col-md-2" style="padding-top: 25px;">
        <strong style="font-size: 20px;"><?= $pagetitle ?></strong>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>From Date</label>
            <input type="date" class="form-control" name="fromdate" id="fromdate"
                   value="<?= !empty($fromdate) ? htmlspecialchars($fromdate) : '' ?>">
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>To Date</label>
            <input type="date" class="form-control" name="todate" id="todate"
                   value="<?= !empty($todate) ? htmlspecialchars($todate) : '' ?>">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Search</label>
            <input type="text" class="form-control" name="search"
                   placeholder="Client / Doctor / Slot"
                   value="<?= !empty($search) ? htmlspecialchars($search) : '' ?>">
        </div>
    </div>

    <div class="col-md-3 text-right">
        <div class="form-group" style="padding-top: 25px;">
            <button type="submit" name="submit" value="Filter" class="btn bt-add-appoin">
                <i class="fa fa-search"></i> Filter
            </button>

            <a href="<?= base_url(); ?>appointment" class="btn bt-add-appoin" style="margin-left: 5px;">
                <i class="fa fa-refresh"></i> Show All
            </a>
        </div>
    </div>
</div>
                            <?= form_close(); ?>

                            <!-- Appointment Table -->
                            <table class="table table-striped table-modern" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="5%">Sr. No.</th>
                                        <th width="10%">Date</th>
                                        <th width="10%">Slot</th>
                                        <th>Client Name</th>
                                        <th>Mobile</th>
                                        <th>Plan Name</th>
                                        <th>Doctor</th>
                                        <th>Designation</th>
                                        <th>Experience</th>
                                        <th>Comment</th>
                                        <th width="8%">Status</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (!empty($appointmentList) && count($appointmentList) > 0): ?>
                                        <?php $k = 1 + ($offset ?? 0); ?>
                                        <?php foreach ($appointmentList as $row): ?>
                                            <tr>
                                                <td><?= $k; ?></td>
                                                <td><?= !empty($row->date) ? date('d/m/Y', strtotime($row->date)) : 'N/A'; ?></td>
                                                <td><?= !empty($row->slot) ? htmlspecialchars($row->slot) : 'N/A'; ?></td>
                                                <td><?= !empty($row->client_name) ? htmlspecialchars($row->client_name) : 'N/A'; ?></td>
                                                <td><?= !empty($row->client_mobile_number) ? htmlspecialchars($row->client_mobile_number) : 'N/A'; ?></td>
                                                <td><?= !empty($row->name) ? htmlspecialchars($row->name) : 'N/A'; ?></td>
                                                <td><?= !empty($row->doctor_name) ? htmlspecialchars($row->doctor_name) : 'N/A'; ?></td>
                                                <td><?= !empty($row->designation) ? htmlspecialchars($row->designation) : 'N/A'; ?></td>
                                                <td>
                                                    <?php if (!empty($row->years) || !empty($row->month)): ?>
                                                        <?= ($row->years ?? 0) ?> year<?= ($row->years != 1) ? 's' : '' ?> 
                                                        & <?= ($row->month ?? 0) ?> month<?= ($row->month != 1) ? 's' : '' ?>
                                                    <?php else: ?>
                                                        N/A
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row->comment)): ?>
                                                        <span title="<?= htmlspecialchars($row->comment) ?>">
                                                            <?= strlen($row->comment) > 30 ? substr($row->comment, 0, 30) . '...' : $row->comment ?>
                                                        </span>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row->status)): ?>
                                                        <?php 
                                                            $app_status = $row->status;
                                                            if ($app_status == 'Upcoming' && !empty($row->date) && strtotime($row->date) < strtotime('today')) {
                                                                $app_status = 'Missed';
                                                            }
                                                        ?>
                                                        <span class="label label-<?= 
                                                            $app_status == 'Active' ? 'success' : 
                                                            ($app_status == 'Completed' ? 'info' : 
                                                            ($app_status == 'Upcoming' ? 'warning' : 
                                                            ($app_status == 'Missed' ? 'default' : 'danger'))) 
                                                        ?>">
                                                            <?= htmlspecialchars($app_status) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        N/A
                                                    <?php endif; ?>
                                                </td>
                                                <td class="action-buttons">
                                                    <?php if (function_exists('CheckAccess') && CheckAccess(class_basename(service('router')->controllerName()), 'close')): ?>
                                                        <?php if ($row->status != 'Cancelled'): ?>
                                                            <a href="javascript:void(0)" onclick="close_appointment(<?= $row->appointment_id ?>)"
                                                               class="btn btn-xs red" title="Cancel Appointment">
                                                                <i class="fa fa-times"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                    <?php if (function_exists('CheckAccess') && CheckAccess(class_basename(service('router')->controllerName()), 'listcomment')): ?>
                                                        <a href="<?= base_url(); ?>appointment/comment/<?= $row->appointment_id; ?>"
                                                           class="btn btn-xs yellow" title="View Comments">
                                                            <i class="fa fa-comment"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <a href="<?= base_url(); ?>appointment/clientdashboard/<?= $row->client_id; ?>"
                                                       class="btn btn-xs blue" title="Client Dashboard">
                                                        <i class="fa fa-dashboard"></i>
                                                    </a>

                                                    <?php if (function_exists('CheckAccess') && CheckAccess(class_basename(service('router')->controllerName()), 'addplan')): ?>
                                                        <a href="<?= base_url(); ?>appointment/addplan/<?= $row->appointment_id; ?>"
                                                           class="btn btn-xs green" title="Add Plans">
                                                            <i class="fa fa-file-text"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php $k++; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="12" class="no-data-row">
                                                <div style="padding: 30px;">
                                                    <i class="fa fa-calendar-times-o fa-3x" style="color: #ccc; margin-bottom: 15px;"></i><br>
                                                    <h4>No appointments found</h4>
                                                    <p>
                                                        <?php if (!empty($search) || !empty($fromdate) || !empty($todate)): ?>
                                                            No appointments match your current filters.
                                                            <br>
                                                            <a href="<?= base_url(); ?>appointment" class="btn btn-default" style="margin-top: 10px;">
                                                                Clear Filters
                                                            </a>
                                                        <?php else: ?>
                                                            There are no appointments scheduled at the moment.
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <?php if (!empty($appointmentList) && count($appointmentList) > 0 && isset($pager)): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="dataTables_info">
                                            Showing <?= ($offset ?? 0) + 1 ?> to 
                                            <?= min(($offset ?? 0) + count($appointmentList), ($total ?? 0)) ?> 
                                            of <?= $total ?? 0 ?> entries
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <?php if (isset($pager)): ?>
                                            <?= $pager->links() ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Close Appointment Modal -->
    <div class="modal fade" id="close_appointment" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Cancel Appointment</h4>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this appointment?</p>
                    <p class="text-warning"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn default" data-dismiss="modal">No, Keep It</button>
                    <button type="button" class="btn btn-danger" id="confirmCancel">Yes, Cancel Appointment</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentAppointmentId = null;
        
        function close_appointment(appointment_id) {
            currentAppointmentId = appointment_id;
            $('#close_appointment').modal('show');
        }
        
        $(document).ready(function() {
            $('#confirmCancel').click(function() {
                if (!currentAppointmentId) return;
                
                $.post('<?= base_url("appointment/close") ?>', { 
                    appointment_id: currentAppointmentId,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                }, function(response) {
                    if (response == 1) {
                        $('#close_appointment').modal('hide');
                        showNotification('Appointment cancelled successfully', 'success');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification('Failed to cancel appointment', 'error');
                    }
                }).fail(function() {
                    showNotification('Error processing request', 'error');
                });
            });
            
            // Date validation
            $('#fromdate, #todate').change(function() {
                var fromDate = $('#fromdate').val();
                var toDate = $('#todate').val();
                
                if (fromDate && toDate && fromDate > toDate) {
                    alert('From date cannot be later than To date');
                    $(this).val('');
                }
            });
        });
        
        function showNotification(message, type) {
            // You can implement a toast notification here
            alert(message);
        }
    </script>

</div>

<?= $this->endSection(); ?>