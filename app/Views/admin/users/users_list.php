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

                            <!-- ====== TITLE + ADD USER BUTTON ROW ====== -->
                            <div class="table-toolbar">
                                <div style="display:flex; align-items:center; justify-content:space-between;">

                                    <!-- Left: Page Title -->
                                    <h3 style="margin:0; padding:10px 0;"><?= $pagetitle ?></h3>

                                    <!-- Right: Add User Button -->
                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                        <a href="<?= base_url(); ?>users/add" class="btn green">
                                            Add User <i class="fa fa-plus"></i>
                                        </a>
                                    <?php } ?>

                                </div>
                            </div>
                            <!-- ====== END TOOLBAR ROW ====== -->

                            <!-- ====== USERS LIST TABLE ====== -->
                            <table class="table table-striped table-modern " id="sample_1">
                                <thead>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Full Name</th>
                                        <th>Phone No</th>
                                        <th>Email</th>
                                        <th>User Role</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (count($usersList) > 0) {
                                        $k = 1;
                                        foreach ($usersList as $row) { ?>
                                            <tr class="odd gradeX">
                                                <td><?= $k; ?></td>
                                                <td><?= $row->first_name . ' ' . $row->last_name; ?></td>
                                                <td><?= $row->phone_number; ?></td>
                                                <td><?= $row->email; ?></td>
                                                <td><?= $row->user_role; ?></td>

                                                <td width="16%">
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'view')) { ?>
                                                        <a href="<?= base_url(); ?>users/view/<?= $row->uid; ?>" class="btn default btn-xs green" title="View User">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    <?php } ?>

                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                        <a href="<?= base_url(); ?>users/edit/<?= $row->uid; ?>" class="btn default  purple" title="Edit User">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    <?php } ?>

                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { 
                                                        if (strtolower($row->user_role) != 'admin') {
                                                    ?>
                                                        <a href="javascript:void(0)" onclick="close_user(<?= $row->uid; ?>)" class="btn default btn-xs red" title="Delete User">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php } } ?>

                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'role')) { ?>
                                                        <a href="<?= base_url(); ?>users/role/<?= $row->uid; ?>" class="btn default btn-xs dark" title="User Role">
                                                            <i class="fa fa-gear"></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                    <?php $k++; } } ?>
                                </tbody>
                            </table>

                            <!-- ====== PAGINATION ====== -->
                            <?php 
                            $pager = \Config\Services::pager();
                            if ($pager):
                                $pagi_path = 'food';
                                $pager->setPath($pagi_path);
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

                        </div> 
                    </div> <!-- END PORTLET -->

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====== DELETE USER MODAL ====== -->
<div class="modal fade" id="close_user" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Close User</h4>
            </div>
            <div class="modal-body">
                Are you sure to close this User?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<!-- ====== DELETE USER SCRIPT ====== -->
<script type="text/javascript">
function close_user(uid) {
    $('#close_user').modal();
    $('#close_user #yes').off('click').on('click', function() {
        var url = '<?= base_url('users/close') ?>';

        $.post(url, { uid: uid }, function(responseText) {
            if (responseText == 1) {
                location.href = '<?= base_url('users') ?>';
            } else {
                alert('Something went wrong with your request');
            }
        });
    });
}
</script>

<?= $this->endSection(); ?>
