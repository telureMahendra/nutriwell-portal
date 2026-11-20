<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />

<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation = \Config\Services::validation();

$totalCount = $totalClientsCount;
?>

<style>
    .pagination {
        margin: 20px 0;
        float: left;
    }

    .pager {
        float: right;
    }

    /* Prevent overflow and wrap long text */
    .table td {
        word-wrap: break-word;
        max-width: 150px;
        /* adjust for email or name columns */
    }

    .hidden-xs {
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
    }

    /* Styling for merged contact info */
    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .contact-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        line-height: 1.4;
        padding: 6px 8px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .contact-item:hover {
        background-color: #f8f9fa;
    }

    .contact-icon {
        font-size: 14px;
        width: 16px;
        text-align: center;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .contact-email-wrapper {
        background-color: #e8f4ff;
        border-left: 3px solid #3498db;
    }

    .contact-email-wrapper .contact-icon {
        color: #3498db;
    }

    .contact-email {
        font-weight: 500;
        color: #2c3e50;
        word-break: break-all;
        font-size: 0.9em;
    }

    .contact-phone-wrapper {
        background-color: #f0f8f0;
        border-left: 3px solid #27ae60;
    }

    .contact-phone-wrapper .contact-icon {
        color: #27ae60;
    }

    .contact-phone {
        font-size: 0.9em;
        color: #2c3e50;
        word-break: break-all;
        font-weight: 500;
    }

    /* Plan info styling */
    .plan-info {
        font-size: 0.85em;
        line-height: 1.3;
    }

    .plan-name {
        font-weight: bold;
        color: #2c3e50;
    }

    .plan-dates {
        color: #7f8c8d;
        font-size: 0.8em;
    }

    /* Status badges */
    .status-badge {
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: bold;
        display: inline-block;
        font-size: 0.85em;
    }

    .status-paid {
        background: #c8f7c5;
        color: #1e7a1e;
    }

    .status-expired {
        background: #ffe2b5;
        color: #b36a00;
    }

    .status-unpaid {
        background: #f7c5c5;
        color: #b12d1d;
    }

    /* Filter Tabs Styling */
    .btn-group-justified {
        display: flex;
        width: 100%;
        margin-bottom: 20px;
    }

    .btn-group-justified .btn {
        flex: 1;
        border-radius: 4px !important;
        margin: 0 2px;
        padding: 10px 5px;
        font-weight: 600;
        border: 1px solid #ddd;
    }

    .btn-group-justified .btn:first-child {
        margin-left: 0;
    }

    .btn-group-justified .btn:last-child {
        margin-right: 0;
    }

    .btn-group-justified .btn.active {
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.2);
        position: relative;
        border-width: 2px;
    }

    .btn-group-justified .btn.active:after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 3px;
        background: #333;
    }

    .btn-group-justified .btn-default {
        background: #f8f9fa;
        color: #333;
    }

    .btn-group-justified .btn-default.active {
        background: #e9ecef;
        border-color: #6c757d;
    }

    .btn-group-justified .btn-success {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }

    .btn-group-justified .btn-success.active {
        background: #218838;
        border-color: #1e7e34;
    }

    .btn-group-justified .btn-warning {
        background: #ffc107;
        color: #212529;
        border-color: #ffc107;
    }

    .btn-group-justified .btn-warning.active {
        background: #e0a800;
        border-color: #d39e00;
    }

    .btn-group-justified .btn-primary {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }

    .btn-group-justified .btn-primary.active {
        background: #0069d9;
        border-color: #0062cc;
    }

    .btn-group-justified .btn-danger {
        background: #dc3545;
        color: white;
        border-color: #dc3545;
    }

    .btn-group-justified .btn-danger.active {
        background: #c82333;
        border-color: #bd2130;
    }

    /* Total Count Badge */
    .total-count-badge {
        background: #3498db;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 14px;
        display: inline-block;
        margin-left: 10px;
    }

    /* Mobile-friendly buttons */
    @media (max-width: 767px) {
        .table td .btn {
            margin-bottom: 3px;
        }

        .btn-group-justified {
            flex-wrap: wrap;
        }

        .btn-group-justified .btn {
            flex: 0 0 calc(50% - 4px);
            margin: 2px;
            padding: 8px 3px;
            font-size: 0.9em;
        }

        .total-count-badge {
            display: block;
            margin: 10px auto;
            text-align: center;
            width: fit-content;
        }
    }
</style>

<div class="content">
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <?= $pagetitle ?>
                                <span class="total-count-badge">
                                    <i class="fa fa-users"></i>
                                    Total: <?= $totalClientsCount; ?>
                                    <?php if ($totalPages > 1): ?>
                                        <small>(Page <?= $currentPage; ?> of <?= $totalPages; ?>)</small>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <!-- Filter Tabs Section -->
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Filter Tabs -->
                                    <div class="btn-group btn-group-justified mb-3" role="group"
                                        aria-label="Client filters">
                                        <a href="<?= base_url('clients'); ?>"
                                            class="btn btn-default <?= (empty($status_filter) && empty($payment_status_filter)) ? 'active' : ''; ?>">
                                            All Clients
                                        </a>
                                        <a href="<?= base_url('clients?status=Active'); ?>"
                                            class="btn btn-success <?= ($status_filter == 'Active') ? 'active' : ''; ?>">
                                            Active
                                        </a>
                                        <a href="<?= base_url('clients?status=Inactive'); ?>"
                                            class="btn btn-warning <?= ($status_filter == 'Inactive') ? 'active' : ''; ?>">
                                            Inactive
                                        </a>
                                        <a href="<?= base_url('clients?payment_status=paid'); ?>"
                                            class="btn btn-primary <?= ($payment_status_filter == 'paid') ? 'active' : ''; ?>">
                                            Paid
                                        </a>
                                        <a href="<?= base_url('clients?payment_status=unpaid'); ?>"
                                            class="btn btn-danger <?= ($payment_status_filter == 'unpaid') ? 'active' : ''; ?>">
                                            Unpaid
                                        </a>
                                    </div>

                                    <!-- Stats Cards -->
                                    <div class="row" style="margin-bottom:15px;">
                                        <div class="col-md-4 col-sm-4 col-xs-12" style="padding:0 6px;">
                                            <a href="<?= base_url('clients'); ?>" style="text-decoration:none;">
                                                <div style="background:#3498db;color:white;padding:12px 15px;border-radius:6px;text-align:center;cursor:pointer;">
                                                    <div style="font-size:26px;font-weight:bold;"><?= $totalClientsCount; ?></div>
                                                    <div style="font-size:12px;opacity:.9;"><i class="fa fa-users"></i> Total Clients</div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4 col-sm-4 col-xs-12" style="padding:0 6px;">
                                            <a href="<?= base_url('clients?payment_status=paid'); ?>" style="text-decoration:none;">
                                                <div style="background:#27ae60;color:white;padding:12px 15px;border-radius:6px;text-align:center;cursor:pointer;">
                                                    <div style="font-size:26px;font-weight:bold;"><?= $footerActivePlans; ?></div>
                                                    <div style="font-size:12px;opacity:.9;"><i class="fa fa-check-circle"></i> Active Plans</div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4 col-sm-4 col-xs-12" style="padding:0 6px;">
                                            <a href="<?= base_url('clients?payment_status=unpaid'); ?>" style="text-decoration:none;">
                                                <div style="background:#e74c3c;color:white;padding:12px 15px;border-radius:6px;text-align:center;cursor:pointer;">
                                                    <div style="font-size:26px;font-weight:bold;"><?= $footerNoActivePlans; ?></div>
                                                    <div style="font-size:12px;opacity:.9;"><i class="fa fa-times-circle"></i> No Active Plans</div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Current Filter Status -->
                                    <?php if (!empty($status_filter) || !empty($payment_status_filter)): ?>
                                        <div class="alert alert-info" style="padding: 8px 15px; margin-bottom: 15px;">
                                            <i class="fa fa-filter"></i>
                                            Showing:
                                            <?php
                                            if (!empty($status_filter)) {
                                                echo '<strong>' . $status_filter . '</strong> Clients';
                                            }
                                            if (!empty($payment_status_filter)) {
                                                echo '<strong>' . ucfirst($payment_status_filter) . '</strong> Clients';
                                            }
                                            ?>
                                            <span class="pull-right">
                                                <span class="total-count-badge"
                                                    style="background: #2c3e50; padding: 3px 10px; font-size: 12px;">
                                                    <i class="fa fa-user"></i> <?= $totalCount; ?> Client(s)
                                                </span>
                                                <a href="<?= base_url('clients'); ?>"
                                                    style="color: #31708f; margin-left: 10px;">
                                                    <i class="fa fa-times"></i> Clear filter
                                                </a>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="table-toolbar">
                                <?php
                                $attributes = array('method' => 'get', 'class' => 'login-form', 'id' => 'loginform');
                                echo form_open('clients', $attributes);
                                ?>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="control-label">From</label>
                                            <input type="date" class="form-control" name="fromdate" id="fromdate"
                                                value="<?php echo ($fromdate != "" ? $fromdate : ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="control-label">To</label>
                                            <input type="date" class="form-control" name="todate"
                                                value="<?php echo ($todate != "" ? $todate : ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3" align="right">
                                        <label class="control-label">&nbsp;&nbsp;</label>
                                        <input type="text" class="form-control" name="search" placeholder="Search"
                                            value="<?php echo $search; ?>">
                                    </div>
                                    <div class="col-md-3" align="right">
                                        <div class="form-group">
                                            <label class="control-label">&nbsp;&nbsp;</label>
                                            <div>
                                                <input type="submit" name="submit" value="Filter" class="btn green" />
                                                <a
                                                    href="<?= base_url(); ?>clients/?download=true&<?php echo $_SERVER["QUERY_STRING"]; ?>"><span
                                                        id="sample_editable_1_new" class="btn green"> &nbsp;&nbsp; <i
                                                            class="fa fa-download"></i> Download &nbsp;&nbsp;</span></a>
                                            </div>
                                        </div>
                                    </div>
                                    <?= form_close(); ?>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-modern" id="sample_1">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Client Number</th>
                                            <th>Client Name</th>
                                            <th>Contact Info</th>
                                            <th>Age</th>
                                            <th>Gender</th>
                                            <th>Booking Status</th>
                                            <th>Plan Info</th>
                                            <th>Sales Agent</th>
                                            <th>Created On</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($clientsList) > 0):
                                            $k = 1;
                                            foreach ($clientsList as $row): ?>
                                                <tr class="odd gradeX">
                                                    <td><?= $k; ?></td>
                                                    <td><?= $row->client_number; ?></td>
                                                    <td><?= $row->client_name; ?></td>
                                                    <td style="word-break:break-word; max-width:220px;">
                                                        <div class="contact-info">
                                                            <div class="contact-item contact-email-wrapper">
                                                                <span class="contact-icon">
                                                                    <i class="fa fa-envelope"></i>
                                                                </span>
                                                                <span class="contact-email"><?= $row->client_email; ?></span>
                                                            </div>
                                                            <div class="contact-item contact-phone-wrapper">
                                                                <span class="contact-icon">
                                                                    <i class="fa fa-phone"></i>
                                                                </span>
                                                                <span
                                                                    class="contact-phone"><?= $row->client_mobile_number; ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= $row->age; ?></td>
                                                    <td><?= $row->client_gender; ?></td>
                                                    <td>
                                                        <?php
                                                        if ($row->bookStatus == 'Active') {
                                                            echo '<span class="status-badge status-paid">Active</span>';
                                                        } elseif (!empty($row->plan_expiry_date) && time() > strtotime($row->plan_expiry_date)) {
                                                            echo '<span class="status-badge status-expired">Expired</span>';
                                                        } else {
                                                            echo '<span class="status-badge status-unpaid">Unpaid</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td style="max-width: 180px;">
                                                        <div class="plan-info">
                                                            <?php if (!empty($row->plan_expiry_date)): ?>
                                                                <div class="plan-dates">
                                                                    <?php if ($row->bookStatus == 'Active'): ?>
                                                                        <span class="text-success"><i class="fa fa-check-circle"></i>
                                                                            Active Plan</span>
                                                                    <?php endif; ?>
                                                                    <br>
                                                                    Expires:
                                                                    <?= date('d/m/Y', strtotime($row->plan_expiry_date)); ?>
                                                                    <?php
                                                                    if (!empty($row->plan_expiry_date)) {
                                                                        $expiryDate = new DateTime($row->plan_expiry_date);
                                                                        $today = new DateTime();
                                                                        $interval = $today->diff($expiryDate);
                                                                        $daysRemaining = $interval->days;
                                                                        if ($interval->invert) {
                                                                            $daysRemaining = -$daysRemaining;
                                                                        }

                                                                        if ($daysRemaining > 0) {
                                                                            echo '<br><small class="text-info">(' . $daysRemaining . ' days left)</small>';
                                                                        } elseif ($daysRemaining == 0) {
                                                                            echo '<br><small class="text-warning">(Expires today)</small>';
                                                                        } else {
                                                                            echo '<br><small class="text-danger">(Expired ' . abs($daysRemaining) . ' days ago)</small>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <span style="color: #95a5a6; font-style: italic;">No Active
                                                                    Plan</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td><?= $row->sales_agent; ?></td>
                                                    <td><?= date('d/m/Y', strtotime($row->created_on)); ?></td>
                                                    <td>
                                                        <!-- Actions for desktop -->
                                                        <div class="hidden-xs">
                                                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'view')): ?>
                                                                <?php if ($row->bookStatus != 'Active'): ?>
                                                                    <a href="<?= base_url('clients/add_subscription/' . $row->client_id); ?>"
                                                                        class="btn purple-plum btn-sm" title="Add Subscription"><i
                                                                            class="fa fa-plus-circle"></i></a>
                                                                <?php endif; ?>
                                                                <a href="<?= base_url(); ?>clients/view/<?= $row->client_id; ?>"
                                                                    class="btn btn-default btn-sm" title="View Client"><i
                                                                        class="fa fa-eye"></i></a>
                                                            <?php endif; ?>
                                                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')): ?>
                                                                <a href="<?= base_url(); ?>clients/edit/<?= $row->client_id; ?>"
                                                                    class="btn btn-default btn-sm" title="Edit Client"><i
                                                                        class="fa fa-edit"></i></a>
                                                            <?php endif; ?>
                                                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'listcomment')): ?>
                                                                <a href="<?= base_url(); ?>clients/listcomment/<?= $row->client_id; ?>"
                                                                    class="btn btn-default btn-sm" title="Comment"><i
                                                                        class="fa fa-comment"></i></a>
                                                            <?php endif; ?>
                                                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')): ?>
                                                                <a href="javascript:void(0)"
                                                                    onclick="close_client(<?= $row->client_id; ?>)"
                                                                    class="btn btn-danger btn-sm" title="Delete Client"><i
                                                                        class="fa fa-trash"></i></a>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Actions for mobile -->
                                                        <div class="visible-xs">
                                                            <div class="btn-group">
                                                                <button type="button"
                                                                    class="btn btn-xs btn-primary dropdown-toggle"
                                                                    data-toggle="dropdown">
                                                                    Actions <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu pull-right" role="menu">
                                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'view')): ?>
                                                                        <?php if ($row->bookStatus != 'Active'): ?>
                                                                            <li><a
                                                                                    href="<?= base_url('clients/add_subscription/' . $row->client_id); ?>">Add Subscription</a>
                                                                            </li>
                                                                        <?php endif; ?>
                                                                        <li><a
                                                                                href="<?= base_url(); ?>clients/view/<?= $row->client_id; ?>">View</a>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')): ?>
                                                                        <li><a
                                                                                href="<?= base_url(); ?>clients/edit/<?= $row->client_id; ?>">Edit</a>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'listcomment')): ?>
                                                                        <li><a
                                                                                href="<?= base_url(); ?>clients/listcomment/<?= $row->client_id; ?>">Comment</a>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')): ?>
                                                                        <li><a href="javascript:void(0)"
                                                                                onclick="close_client(<?= $row->client_id; ?>)">Delete</a>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php $k++; endforeach; else: ?>
                                            <tr>
                                                <td colspan="11" class="text-center">
                                                    <div class="alert alert-warning" style="margin: 20px;">
                                                        <i class="fa fa-exclamation-triangle"></i>
                                                        No clients found with the selected filters.
                                                        <br>
                                                        <small>Try changing your filter criteria or <a
                                                                href="<?= base_url('clients'); ?>">clear all
                                                                filters</a>.</small>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php
                            $pager = \Config\Services::pager();
                            if ($pager):
                                $pager->setPath('clients');
                                $pager->only(['per_page', 'status', 'payment_status', 'fromdate', 'todate', 'search', 'submit']);
                                $fromRecord = (($currentPage - 1) * $perPage) + 1;
                                $toRecord   = min($currentPage * $perPage, $totalClientsCount);
                                ?>
                                <div class="row" style="margin-top:12px; display:flex; align-items:center; flex-wrap:wrap;">
                                    <div class="col-md-3 col-sm-4 col-xs-12" style="margin-bottom:8px;">
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <label style="margin:0; white-space:nowrap; font-weight:600; color:#555;">Show:</label>
                                            <select onchange="changePerPage(this.value)" class="form-control input-sm" style="width:70px; display:inline-block;">
                                                <?php foreach ([10, 20, 50, 100] as $opt): ?>
                                                    <option value="<?= $opt; ?>" <?= $perPage == $opt ? 'selected' : ''; ?>><?= $opt; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span style="white-space:nowrap; color:#888; font-size:12px;">per page</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-4 col-xs-12 text-center" style="margin-bottom:8px;">
                                        <small style="color:#666;">
                                            Showing <strong><?= $fromRecord; ?></strong>–<strong><?= $toRecord; ?></strong> of <strong><?= $totalClientsCount; ?></strong>
                                        </small>
                                    </div>
                                    <div class="col-md-6 col-sm-4 col-xs-12 text-right" style="margin-bottom:8px;">
                                        <?php echo $pager->links(); ?>
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
<div class="modal fade" id="close_client" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Close Client</h4>
            </div>
            <div class="modal-body">
                Are you sure to close this Client?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function close_client(client_id) {
        $('#close_client').modal();
        $('#close_client #yes').click(function () {
            var url = '<?= base_url('clients/close') ?>';
            $.post(url, {
                client_id: client_id,
            }, function (responseText) {
                if (responseText == 1) {
                    location.href = '<?= base_url('clients/') ?>';
                } else {
                    alert('Something went wrong with your request');
                }
            });
        });
    }

    // Auto-set today's date in "To" field if "From" is selected
    document.getElementById('fromdate').addEventListener('change', function () {
        var fromDate = this.value;
        if (fromDate) {
            var toDateField = document.querySelector('input[name="todate"]');
            if (!toDateField.value) {
                toDateField.value = fromDate;
            }
        }
    });

    function changePerPage(value) {
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.delete('page'); // reset to page 1
        window.location.href = url.toString();
    }
</script>

<?= $this->endSection(); ?>