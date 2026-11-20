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

                        <!-- Portlet Title: Food Type left, Add button right -->
                        <div class="portlet-title clearfix">
                            <div class="caption pull-left" style="font-size:16px; line-height:34px;">
                                <?= $pagetitle ?>
                            </div>
                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add_type')) { ?>
                                <div class="pull-right">
                                    <a href="<?= base_url();?>food/add_type" class="btn green" style="margin-top:0;">
                                        Add Food Type <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="portlet-body">
                            <table class="table table-striped table-modern" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="10%">Sr. No.</th>
                                        <th>Food Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($food_type) > 0) { 
                                        $k = 1;
                                        foreach ($food_type as $row) { ?>
                                            <tr class="odd gradeX">
                                                <td><?= $k; ?></td>
                                                <td><?= $row->food_type_name; ?></td>
                                                <td width="15%">
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit_type')) { ?>
                                                        <a href="<?= base_url(); ?>food/edit_type/<?= $row->food_type_id; ?>" 
                                                           class="btn default btn-xs purple" title="Edit Food Type">
                                                           <i class="fa fa-edit"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close_type')) { ?>
                                                        <a href="javascript:void(0)" onclick="close_food_type(<?= $row->food_type_id; ?>)" 
                                                           class="btn default btn-xs red" title="Delete Food Type">
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

<!-- Close Food Type Modal -->
<div class="modal fade" id="close_food_type" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Close Food Type</h4>
            </div>
            <div class="modal-body">
                Are you sure to close this Food Type?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function close_food_type(food_type_id) {
    $('#close_food_type').modal();
    $('#close_food_type #yes').click(function () {
        var url = '<?= base_url('food/close_type') ?>';
        $.post(url, { food_type_id: food_type_id }, function (responseText) {
            if (responseText == 1) {
                location.href = '<?= base_url('food/list_type') ?>';
            } else {
                alert('Something went wrong with your request');
            }
        });
    });
}
</script>

<?= $this->endSection(); ?>
