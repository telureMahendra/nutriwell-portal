<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>

<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>

<div class="content">
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">

                    <div class="portlet box grey-cascade">

                        <!--========================
                            TITLE LEFT + BUTTON RIGHT
                        ==========================-->
                        <div class="portlet-title clearfix">
                            <div class="caption pull-left">
                                <?= $pagetitle ?>
                            </div>

                            <div class="pull-right">
                                <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                    <a href="<?= base_url();?>city/add" class="btn green">
                                        Add City <i class="fa fa-plus"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="portlet-body">

                            <table class="table table-striped table-modern" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="5%">Sr. No.</th>
                                        <th width="20%">City</th>
                                        <th width="7%">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (count($cityList) > 0) { 
                                        $k = 1;
                                        foreach ($cityList as $row) { ?>
                                            <tr class="odd gradeX">
                                                <td><?= $k; ?></td>
                                                <td><?= $row->city_name; ?></td>
                                                <td>
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                        <a href="<?= base_url(); ?>city/edit/<?= $row->city_id ; ?>" 
                                                            class="btn default btn-xs purple" 
                                                            title="Edit City">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    <?php } ?>

                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { ?>
                                                        <a href="javascript:void(0)" 
                                                            onclick="close_city(<?= $row->city_id; ?>)" 
                                                            class="btn default btn-xs red" 
                                                            title="Delete City">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                    <?php $k++; } } ?>
                                </tbody>
                            </table>

                            <?php 
                            $pager = \Config\Services::pager();
                            if ($pager):
                                $pager->setPath('city');
                            ?>
                                <div class="row">
                                    <div class="col-md-6 text-center">
                                        <?= $pager->links(); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?= $pager->simpleLinks(); ?>
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

<!-- CLOSE CITY MODAL -->
<div class="modal fade" id="close_city" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Close City</h4>
            </div>
            <div class="modal-body">
                Are you sure to close this City?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<script>
function close_city(city_id) {
    $("#close_city").modal("show");

    $("#yes").off("click").on("click", function() {
        $.post("<?= base_url('city/close') ?>", { city_id: city_id }, function(res) {
            if (res == 1) {
                location.href = "<?= base_url('city/') ?>";
            } else {
                alert("Something went wrong!");
            }
        });
    });
}
</script>

<?= $this->endSection(); ?>
