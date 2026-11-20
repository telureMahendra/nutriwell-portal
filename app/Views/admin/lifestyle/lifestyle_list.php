<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />

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

                        <!-- Portlet Title: Lifestyle left, Add button right -->
                        <div class="portlet-title clearfix">
                            <div class="caption pull-left" style="font-size:16px; line-height:34px;">
                                <?= $pagetitle ?>
                            </div>
                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                <div class="pull-right">
                                    <a href="<?= base_url(); ?>lifestyle/add" class="btn green" style="margin-top:0;">
                                        Add Lifestyle <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="portlet-body">
                            <table class="table table-striped table-modern" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="5%">Sr. No.</th>
                                        <th width="20%">Lifestyle</th>
                                        <th width="40%">Description</th>
                                        <th width="25%">Image</th>
                                        <th width="7%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($lifestyleList) > 0) {
                                        $k = 1;
                                        foreach ($lifestyleList as $row) { ?>
                                            <tr class="odd gradeX">
                                                <td><?= $k; ?></td>
                                                <td><?= $row->name; ?></td>
                                                <td><?= $row->description; ?></td>
                                                <td>
                                                    <img src="<?= base_url() . 'public/uploads/lifestyle/' . $row->image; ?>" 
                                                         alt="Lifestyle Image" height="70" width="100"
                                                         onclick="showLargeImage('<?= base_url() . 'public/uploads/lifestyle/' . $row->image; ?>')" />
                                                </td>
                                                <td width="15%">
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                        <a href="<?= base_url(); ?>lifestyle/edit/<?= $row->lifestyle_id; ?>" 
                                                           class="btn default btn-xs purple" title="Edit Lifestyle">
                                                           <i class="fa fa-edit"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { ?>
                                                        <a href="javascript:void(0)" onclick="close_lifestyle(<?= $row->lifestyle_id; ?>)" 
                                                           class="btn default btn-xs red" title="Delete Lifestyle">
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

<!-- Close Lifestyle Modal -->
<div class="modal fade" id="close_lifestyle" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Close Lifestyle</h4>
            </div>
            <div class="modal-body">
                Are you sure to close this Lifestyle?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<!-- Large Image Modal -->
<div class="modal fade" id="largeImageModal" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="width:60vw;">
            <div class="modal-body">
                <img id="largeImage" src="" alt="Large Lifestyle Image" style="width:100%;height:80vh;" />
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function close_lifestyle(lifestyle_id) {
        $('#close_lifestyle').modal();
        $('#close_lifestyle #yes').click(function() {
            var url = '<?= base_url('lifestyle/close') ?>';
            $.post(url, { lifestyle_id: lifestyle_id }, function(responseText) {
                if (responseText == 1) {
                    location.href = '<?= base_url('lifestyle/') ?>';
                } else {
                    alert('Something went wrong with your request');
                }
            });
        });
    }

    function showLargeImage(imageUrl) {
        $('#largeImage').attr('src', imageUrl);
        $('#largeImageModal').modal('show');
    }
</script>

<?= $this->endSection(); ?>
