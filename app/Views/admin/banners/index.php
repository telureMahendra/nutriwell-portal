<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

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
                            <!-- ====== TITLE + ADD BUTTON ROW ====== -->
                            <div class="table-toolbar">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <h3 style="margin:0; padding:10px 0;"><?= $pagetitle ?></h3>
                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                        <a href="<?= base_url(); ?>banners/add" class="btn green">
                                            Add Banner <i class="fa fa-plus"></i>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                            <!-- ====== END TOOLBAR ROW ====== -->

                            <table class="table table-striped table-modern " id="sample_1">
                                <thead>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Title</th>
                                        <th>Image</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($bannersList) > 0) {
                                        $k = 1;
                                        foreach ($bannersList as $row) { ?>
                                            <tr class="odd gradeX">
                                                <td><?= $k; ?></td>
                                                <td><?= $row['title']; ?></td>
                                                <td>
                                                    <img src="<?= base_url() . 'public/uploads/banner/' . $row['banner_image']; ?>" width="100" />
                                                </td>
                                                <td><?= $row['status']; ?></td>
                                                <td width="16%">
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                        <a href="<?= base_url(); ?>banners/edit/<?= $row['banner_id']; ?>" class="btn default purple" title="Edit Banner">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    <?php } ?>

                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { ?>
                                                        <a href="javascript:void(0)" onclick="close_banner(<?= $row['banner_id']; ?>)" class="btn default btn-xs red" title="Delete Banner">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                    <?php $k++; } } ?>
                                </tbody>
                            </table>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====== DELETE MODAL ====== -->
<div class="modal fade" id="close_banner" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Close Banner</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this Banner?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function close_banner(id) {
    $('#close_banner').modal();
    $('#close_banner #yes').click(function() {
        var url = '<?= base_url('banners/close') ?>';
        $.post(url, { id: id }, function(responseText) {
            if (responseText == 1) {
                location.href = '<?= base_url('banners') ?>';
            } else {
                alert('Something went wrong with your request');
            }
        });
    });
}
</script>

<?= $this->endSection(); ?>
