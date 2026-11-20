<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>

<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>

<style>
/* Title left + Button right in one row */
.portlet-title {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    padding: 15px !important;
}

/* Remove Metronic floats */
.portlet-title .caption,
.portlet-title .btn,
.portlet-title a {
    float: none !important;
}

/* Style title */
.portlet-title .caption {
    font-size: 18px;
    font-weight: 600;
    text-align: left !important;
}

/* Right align button */
.portlet-title .btn {
    text-align: right;
}

/* 🔥 MOST IMPORTANT: Remove Metronic clearfix on portlet-title */
.portlet > .portlet-title:before,
.portlet > .portlet-title:after {
    content: none !important;
    display: none !important;
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

                            <!-- LEFT SIDE: TITLE -->
                            <div class="caption">
                                <?= $pagetitle ?>
                            </div>

                            <!-- RIGHT SIDE: ADD BUTTON -->
                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                <a href="<?= base_url();?>language/add" class="btn green">
                                    Add Language <i class="fa fa-plus"></i>
                                </a>
                            <?php } ?>

                        </div>

                        <div class="portlet-body">
                            <table class="table table-striped table-modern" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="10%">Sr. No.</th>
                                        <th width="60%">Language Name</th>
                                        <th width="10%">Icon</th>
                                        <th width="10%">Status</th>
                                        <th width="5%">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                <?php if (count($languageList) > 0) { 
                                    $k = 1;
                                    foreach ($languageList as $row) { ?>
                                        <tr class="odd gradeX">
                                            <td><?= $k; ?></td>
                                            <td><?= $row->language; ?></td>
                                            <td><img src="<?= base_url('public/uploads/language/'.$row->language_image);?>" width="40px"></td>
                                            <td><?= $row->status; ?></td>

                                            <td width="15%">
                                                <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                    <a href="<?= base_url(); ?>language/edit/<?= $row->language_id; ?>" class="btn default btn-xs purple" title="Edit Language">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                <?php } ?>

                                                <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { ?>
                                                    <a href="javascript:void(0)" onclick="close_language(<?= $row->language_id; ?>)" class="btn default btn-xs red" title="Delete Language">
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

<!-- Delete Modal -->
<div class="modal fade" id="close_language" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Close Language</h4>
            </div>
            <div class="modal-body">
                Are you sure to close this language?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function close_language(language_id) {
    $('#close_language').modal();
    $('#close_language #yes').click(function () {
        var url = '<?= base_url('language/close') ?>';

        $.post(url, { language_id: language_id }, function (responseText) {
            if (responseText == 1) {
                location.href = '<?= base_url('language') ?>';
            } else {
                alert('Something went wrong with your request');
            }
        });
    });
}
</script>

<?= $this->endSection(); ?>
