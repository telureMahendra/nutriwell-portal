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
                        <div class="portlet-title">
                            <div class="caption">
                                <?= $pagetitle ?>
                            </div>

                        </div>
                        <div class="portlet-body">
                            <div class="table-toolbar">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="btn-group">
                                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'postcomment')) { ?>
                                                <a href="javascript:void(0)" onclick="addcomment()"><span id="sample_editable_1_new" class="btn green"> Add Comment <i class="fa fa-plus"></i> </span></a>
                                            <?php } ?>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-modern" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="8%">Sr. No. </th>
                                        <th width="12%">Date</th>
                                        <th>Comment</th>
                                        <!-- <th width="15%">Mentor</th> -->
                                        <th width="8%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($clientcomment) > 0) {
                                        $k = 1;
                                        foreach ($clientcomment as $row) {
                                    ?>
                                            <tr class="odd gradeX">
                                                <td><?= $k; ?></td>
                                                <td><?php echo date('d/m/Y h:i A',strtotime($row->date));?></td>
                                                <td><?= $row->comment; ?></td>
                                                <!-- <td><?= $row->doctor_name; ?></td> -->
                                                <td>
                              
                                                        <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'editcomment')) { ?>
                                                            <a href="javascript:void(0)" onclick="editcomment(<?php echo $row->id; ?>,'<?php echo $row->comment; ?>')" class="btn default btn-xs purple" title="Edit Comment" alt="Edit Food Type"><i class="fa fa-edit"></i></a>
                                                        <?php } ?>

                                                        <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'closecomment')) { ?>
                                                            <a href="javascript:void(0)" onclick="close_clientcomment(<?= $row->id; ?>)" class="btn default btn-xs red" title="Delete Comment" alt="Delete Comment"><i class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                    
                                                </td>
                                            </tr>
                                    <?php
                                            $k++;
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="close_clientcomment" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Close Comment</h4>
            </div>
            <div class="modal-body">
                Are you sure to close this Comment?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="postcomment" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Comment</h4>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Comment<span class="required" aria-required="true"> *</span></label>
                                <textarea name="comment" id="comment" title="Comment" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="comment_id" id="comment_id">
                    <input type="hidden" name="client_id" id="client_id" value="<?php echo $client_id; ?>">
                    <button type="button" class="btn default" id="yes" onclick="postcomment()">Submit</button>
                    <button type="button" class="btn default" id="no" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function close_clientcomment(comment_id) {
        $('#close_clientcomment').modal();
        $('#close_clientcomment #yes').click(function() {
            var url = '<?= base_url('clients/closecomment') ?>';
            $.post(url, {
                comment_id: comment_id,
            }, function(responseText) {
                if (responseText == 1) {
                    location.reload(true);
                } else {
                    alert('Something went wrong with your request');
                }
            });
        });
    }

    function addcomment() {
        $('#postcomment').modal();
    }

    function editcomment(comment_id, comment) {
        $('#postcomment').modal();
        $('#comment_id').val(comment_id);
        var client_id = <?php echo $client_id; ?>;
        $('#client_id').val(client_id);
        $('#comment').val(comment);
    }

    function postcomment() {
        var comment_id = $('#comment_id').val();
        var client_id = <?php echo $client_id; ?>;
        var comment = $('#comment').val();
        $.ajax({
            url: '<?= base_url('clients/postcomment') ?>',
            type: 'POST',
            data: {
                comment_id: comment_id,
                client_id: client_id,
                comment: comment
            },
            success: function(response) {
                if (response == 1) {
                    location.reload(true);
                } else {
                    alert('Something went wrong with your request');
                }
            }
        });
    }
</script>
<?= $this->endSection(); ?>