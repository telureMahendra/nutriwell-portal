<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>

<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>
<style>
    .pagination{
        margin: 20px 0;
        float: left;
    }
    .pager{
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
                        <div class="portlet-title">
                            <div class="caption">
                                <?= $pagetitle?>
                            </div>

                        </div>
                        <div class="portlet-body">
                            <div class="table-toolbar">
                                <div class="row">
                                <?php 
                                    $attributes = array('class' => 'login-form', 'id' => 'loginform','method'=>'get');
                                    echo form_open('doctor', $attributes);
                                ?>
                                    <div class="col-md-3">
                                        <label class="control-label">Designation</label>
                                        <?= form_dropdown('designation',$allDesignation,$designation,'class="select2me form-control" tabindex="0"'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label"><br></label>
                                            <div>
                                                <input  type="submit" name="submit" value="Filter"   class="btn green"/>
                                                <input  type="submit" name="reset" value="Reset"   class="btn btn-wht"/>
                                            </div>
                                        </div>
                                    </div>
                                <?= form_close(); ?>
                                    <div class="col-md-3" align="right">
                                        <label class="control-label"><br></label>
                                        <div>
                                            <div class="btn-group">
                                                <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                                <a href="<?= base_url();?>doctor/add"><span id="sample_editable_1_new" class="btn green"> Add Mentor <i class="fa fa-plus"></i> </span></a>
                                                <?php }?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-hover" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                         Sr. No. 
                                     </th>
                                     <th width="20%">
                                     Mentor Name
                                    </th>
                                    <th>Gender</th>
                                    <th>Phone Number</th>
                                    <th>Email </th>
                                    <th>Designation</th>

                                    <th width="40%">
                                        Description
                                    </th>
                                    <th width="25%">
                                        Image
                                    </th>
                                    <th width="7%">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($doctorList) > 0) { 
                                    $k = 1;
                                    foreach ($doctorList as $row) {
                                        ?>
                                        <tr class="odd gradeX">
                                            <td><?= $k; ?></td>
                                            <td><?= $row->doctor_name; ?></td>
                                            <td><?= $row->gender; ?></td>
                                            <td><?= $row->phone_number; ?></td>
                                            <td><?= $row->email; ?></td>
                                            <td><?= $row->designation; ?></td>
                                            <td><?= $row->description; ?></td>
                                            <td>
                                                <?php $imgSrc = base_url().'public/uploads/doctor/'.$row->image; ?>
                                                <?php $fallback = base_url().'public/assets/admin/layout/img/avatar.png'; ?>
                                                <img src="<?= $imgSrc ?>"
                                                     alt="Mentor Image"
                                                     height="70" width="100"
                                                     style="object-fit:cover;border-radius:4px;"
                                                     onerror="this.onerror=null;this.src='<?= $fallback ?>';"
                                                     onclick="showLargeImage('<?= $imgSrc ?>')" />
                                            </td>
                                            <td width="15%">
                                                <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                <a href="<?= base_url(); ?>doctor/edit/<?= $row->doctor_id;?>" class="btn default btn-xs purple" title="Edit Doctor" alt="Edit Doctor"><i class="fa fa-edit"></i></a>
                                                <?php }?>

                                                <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'close')) { ?>
                                                <a href="javascript:void(0)" onclick="close_doctor(<?= $row->doctor_id; ?>)" class="btn default btn-xs red" title="Delete Mentor" alt="Delete Mentor"><i class="fa fa-trash"></i></a>
                                                <?php }?>
                                            </td>
                                        </tr>
                                        <?php
                                        $k++;
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php 
                            $pager = \Config\Services::pager();
                            if ($pager):
                                $pagi_path = 'doctor';
                                $pager->setPath($pagi_path);
                            ?>
                            <div class="row">
                            <div class="col-md-6" style="text-align:center;">
                                    <?php echo $pager->links();?>
                                </div>

                            <div class="col-md-6">
                                    <?php echo $pager->simpleLinks();?>
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
<div class="modal fade" id="close_doctor" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Close Mentor</h4>
            </div>
            <div class="modal-body">
                Are you sure to close this Mentor?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes">Yes</button>
                <button type="button" class="btn default" id="no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="largeImageModal" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="width:60vw;">
            <div class="modal-body">
                <img id="largeImage" src="" alt="Large Goal Image" style="width:100%;height:80vh;" />
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function close_doctor(doctor_id) {
     $('#close_doctor').modal();
         $('#close_doctor #yes').click(function () {
             var url ='<?= base_url('doctor/close') ?>';
            $.post(url, {
                 doctor_id: doctor_id,
             }, function (responseText) {
                 if (responseText == 1) {
                     location.href = '<?= base_url('doctor/') ?>';
                 } else {
                     alert('Something went wrong with your request');
                 }
             });
         });
    }
</script>
<script type="text/javascript">
    function showLargeImage(imageUrl) {
        $('#largeImage').attr('src', imageUrl);
        $('#largeImageModal').modal('show');
    }
</script>

<?= $this->endSection();?>