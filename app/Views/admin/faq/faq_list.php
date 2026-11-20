<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<style>
/* Flexbox Title + Button in one row */
.portlet-title {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    padding: 15px !important;
}

/* Remove Metronic clearfix (important for flexbox) */
.portlet > .portlet-title:before,
.portlet > .portlet-title:after {
    content: none !important;
    display: none !important;
}

/* Ensure no float breaks flex */
.portlet-title .caption,
.portlet-title a,
.portlet-title .btn {
    float: none !important;
}

.portlet-title .caption {
    font-size: 18px;
    font-weight: 600;
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

                        <!-- TITLE LEFT + BUTTON RIGHT -->
                        <div class="portlet-title">
                            <div class="caption">
                                <?= $pagetitle ?>
                            </div>

                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                <a href="<?= base_url();?>faq/add" class="btn green">
                                    Add Faq <i class="fa fa-plus"></i>
                                </a>
                            <?php } ?>
                        </div>
                        <!-- END TITLE ROW -->

                        <div class="portlet-body">

                            <table class="table table-striped table-modern" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="5%">Sr. No.</th>
                                        <th width="20%">Question</th>
                                        <th width="68%">Answer</th>
                                        <th width="7%">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                <?php if (count($faqList) > 0) { 
                                    $k = 1;
                                    foreach ($faqList as $row) { ?>
                                    
                                        <tr class="odd gradeX">
                                            <td><?= $k; ?></td>
                                            <td><?= $row->question; ?></td>
                                            <td><?= $row->answer; ?></td>
                                            <td width="15%">
                                                
                                                <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                    <a href="<?= base_url();?>faq/edit/<?= $row->faq_id; ?>" 
                                                       class="btn default btn-xs purple" 
                                                       title="Edit Faq">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                <?php } ?>

                                                <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { ?>
                                                    <a href="javascript:void(0)" 
                                                       onclick="close_faq(<?= $row->faq_id; ?>)" 
                                                       class="btn default btn-xs red" 
                                                       title="Delete Faq">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
                                        </tr>

                                <?php $k++; } } else { ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No FAQs found.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>

                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="close_faq" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Delete FAQ</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this FAQ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section("scripts"); ?>
<script type="text/javascript">
function close_faq(faq_id) {
    $('#close_faq').modal();
    $('#close_faq #yes').click(function () {
        $.post('<?= base_url('faq/close') ?>', { faq_id: faq_id }, function (responseText) {
            if (responseText == 1) {
                location.href = '<?= base_url('faq') ?>';
            } else {
                alert('Something went wrong with your request');
            }
        });
    });
}
</script>
<?= $this->endSection(); ?>