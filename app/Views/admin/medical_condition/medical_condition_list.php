<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />

<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation = \Config\Services::validation(); ?>

<style>
    /* PORTLET TITLE FIX */
    .portlet-title {
        padding: 15px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
    }

    .portlet-title .caption {
        font-size: 18px;
        font-weight: 600;
        margin: 0 !important;
        display: flex;
        align-items: center;
    }

    .portlet-title .btn {
        display: flex !important;
        align-items: center !important;
    }
    .portlet > .portlet-title:before, .portlet > .portlet-title:after {
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

                            <!-- LEFT SIDE TITLE -->
                            <div class="caption">
                                <?= $pagetitle ?>
                            </div>

                            <!-- RIGHT SIDE BUTTON -->
                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                <a href="<?= base_url(); ?>medical_condition/add" class="btn green">
                                    Add Medical Condition <i class="fa fa-plus"></i>
                                </a>
                            <?php } ?>

                        </div>

                        <div class="portlet-body">

                            <table class="table table-striped table-modern" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="10%">Sr. No.</th>
                                        <th>Medical Condition</th>
                                        <th>Status</th>
                                        <th width="15%">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (count($medicalList) > 0) {
                                        $k = 1;
                                        foreach ($medicalList as $row) { ?>
                                            <tr>
                                                <td><?= $k; ?></td>
                                                <td><?= $row->title; ?></td>
                                                <td><?= $row->status; ?></td>
                                                <td>

                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                        <a href="<?= base_url(); ?>medical_condition/edit/<?= $row->medical_id; ?>"
                                                           class="btn default btn-xs purple"
                                                           title="Edit">
                                                           <i class="fa fa-edit"></i>
                                                        </a>
                                                    <?php } ?>

                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { ?>
                                                        <a href="javascript:void(0)"
                                                           onclick="close_medical_condition(<?= $row->medical_id; ?>)"
                                                           class="btn default btn-xs red"
                                                           title="Delete">
                                                           <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php } ?>

                                                </td>
                                            </tr>
                                    <?php
                                            $k++;
                                        }
                                    } ?>
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
<div class="modal fade" id="close_medical_condition" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Close Medical Condition</h4>
            </div>

            <div class="modal-body">
                Are you sure to close this medical condition?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" data-dismiss="modal">No</button>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    function close_medical_condition(medical_id) {
        $('#close_medical_condition').modal();
        $('#close_medical_condition #yes').off().click(function() {
            $.post('<?= base_url('medical_condition/close') ?>', {
                medical_id: medical_id,
            }, function(response) {
                if (response == 1) {
                    location.href = '<?= base_url('medical_condition') ?>';
                } else {
                    alert('Something went wrong');
                }
            });
        });
    }
</script>

<?= $this->endSection(); ?>
