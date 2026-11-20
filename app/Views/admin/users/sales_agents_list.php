<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>

<style>
    .pagination {
        margin: 20px 0;
        float: left;
    }
    .pager {
        float: right;
    }
    .status-active {
        color: #26a69a;
        font-weight: bold;
    }
    .status-inactive {
        color: #ef5350;
        font-weight: bold;
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
                        <div class="portlet-body">

                            <!-- ====== TITLE + ADD SALES AGENT BUTTON ROW ====== -->
                            <div class="table-toolbar">
                                <div style="display:flex; align-items:center; justify-content:space-between;">

                                    <!-- Left: Page Title -->
                                    <h3 style="margin:0; padding:10px 0;"><?= $pagetitle ?> (Showing All)</h3>

                                    <!-- Right: Add Sales Agent Button -->
                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                        <a href="<?= base_url(); ?>users/add" class="btn green">
                                            Add Sales Agent <i class="fa fa-plus"></i>
                                        </a>
                                    <?php } ?>

                                </div>
                            </div>
                            <!-- ====== END TOOLBAR ROW ====== -->

                            <!-- ====== SUCCESS MESSAGE ====== -->
                            <?php if (session()->has('suc_msg')): ?>
                                <div class="alert alert-<?= session('msg-type') ?> alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                                    <?= session('suc_msg') ?>
                                </div>
                            <?php endif; ?>

                            <!-- ====== SALES AGENTS LIST TABLE ====== -->
                            <table class="table table-striped table-modern " id="sample_1">
                                <thead>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Full Name</th>
                                        <th>Phone No</th>
                                        <th>Email</th>
                                        <th>User Role</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php 
                                    if (isset($salesAgents) && count($salesAgents) > 0) {
                                        // Calculate starting serial number for pagination
                                        $perPage = $perPage ?? 10;
                                        $currentPage = $currentPage ?? 1;
                                        $counter = 1 + (($currentPage - 1) * $perPage);
                                        
                                        foreach ($salesAgents as $agent) { 
                                            $uid = $agent->uid ?? 0;
                                            $status = $agent->user_status ?? 'Active';
                                            $statusClass = ($status == 'Active') ? 'status-active' : 'status-inactive';
                                            ?>
                                            <tr class="odd gradeX">
                                                <td><?= $counter; ?></td>
                                                <td>
                                                    <?php 
                                                    if (isset($agent->full_name)) {
                                                        echo htmlspecialchars($agent->full_name);
                                                    } else {
                                                        echo htmlspecialchars(($agent->first_name ?? '') . ' ' . ($agent->last_name ?? ''));
                                                    }
                                                    ?>
                                                </td>
                                                <td><?= htmlspecialchars($agent->phone_number ?? ''); ?></td>
                                                <td><?= htmlspecialchars($agent->email ?? ''); ?></td>
                                                <td>
                                                    <?php 
                                                    if (isset($agent->user_role)) {
                                                        // Format role display name
                                                        echo ucwords(str_replace('_', ' ', $agent->user_role));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="<?= $statusClass ?>">
                                                        <?= htmlspecialchars($status); ?>
                                                    </span>
                                                </td>

                                                <td width="16%">
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'view')) { ?>
                                                        <a href="<?= base_url(); ?>users/view/<?= $uid; ?>" class="btn default btn-xs green" title="View Agent">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    <?php } ?>

                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                        <a href="<?= base_url(); ?>users/edit/<?= $uid; ?>" class="btn default  purple" title="Edit Agent">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    <?php } ?>

                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { ?>
                                                        <?php if ($status == 'Active'): ?>
                                                            <a href="javascript:void(0)" onclick="close_user(<?= $uid; ?>)" class="btn default btn-xs red" title="Deactivate Agent">
                                                                <i class="fa fa-ban"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="javascript:void(0)" onclick="activate_user(<?= $uid; ?>)" class="btn default btn-xs green" title="Activate Agent">
                                                                <i class="fa fa-check"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php } ?>

                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'role')) { ?>
                                                        <a href="<?= base_url(); ?>users/role/<?= $uid; ?>" class="btn default btn-xs dark" title="Agent Role">
                                                            <i class="fa fa-gear"></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                    <?php 
                                            $counter++;
                                        } 
                                    } else { ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No sales agents found.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <!-- ====== PAGINATION ====== -->
                            <?php 
                            if (isset($pager) && $pager):
                                $currentPage = $pager->getCurrentPage() ?? $currentPage ?? 1;
                                $perPage = $pager->getPerPage() ?? $perPage ?? 10;
                            ?>
                            <div class="row">
                                <div class="col-md-6" style="text-align:center;">
                                    <?= $pager->links(); ?>
                                </div>

                                <div class="col-md-6">
                                    <?= $pager->simpleLinks(); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- ====== TOTAL COUNT ====== -->
                            <?php if (isset($total)): ?>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <p class="text-muted">
                                        Total Sales Agents: <?= $total ?? 0 ?>
                                        <?php if (isset($activeCount) && isset($inactiveCount)): ?>
                                            (Active: <?= $activeCount ?>, Inactive: <?= $inactiveCount ?>)
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div> 
                    </div> <!-- END PORTLET -->

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====== DEACTIVATE USER MODAL ====== -->
<div class="modal fade" id="close_user" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Deactivate Sales Agent</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to deactivate this Sales Agent?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<!-- ====== ACTIVATE USER MODAL ====== -->
<div class="modal fade" id="activate_user" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Activate Sales Agent</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to activate this Sales Agent?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="activate_yes">Yes</button>
                <button type="button" class="btn default" id="activate_no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<!-- ====== USER STATUS SCRIPT ====== -->
<script type="text/javascript">
// Deactivate user
function close_user(uid) {
    $('#close_user').modal();
    $('#close_user #yes').click(function() {
        var url = '<?= base_url('users/close') ?>';
        $.post(url, { uid: uid }, function(responseText) {
            if (responseText == 1) {
                location.href = '<?= base_url('users/sales-agents') ?>';
            } else {
                alert('Something went wrong with your request');
            }
        });
    });
}

// Activate user
function activate_user(uid) {
    $('#activate_user').modal();
    $('#activate_user #activate_yes').click(function() {
        var url = '<?= base_url('users/activate') ?>';
        $.post(url, { uid: uid }, function(responseText) {
            if (responseText == 1) {
                location.href = '<?= base_url('users/sales-agents') ?>';
            } else {
                alert('Something went wrong with your request');
            }
        });
    });
}
</script>

<?= $this->endSection(); ?>