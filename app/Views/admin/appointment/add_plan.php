<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>
<div class="content">
    <style type="text/css">
    span.help-block{
        color: #f30;
    }
</style>
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="portlet box grey-cascade">
                <div class="portlet-title">
                    <div class="caption">
                        <?= $pagetitle?>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                        <a href="javascript:;" class="reload"></a>
                        <a href="javascript:;" class="remove"></a>
                    </div>
                </div>
                <div class="portlet-body form">
                <?php 
                    $attributes = array('class' => 'login-form', 'id' => 'loginform');
                    echo form_open_multipart('appointment/addplan/'.$appointment_id, $attributes);

                
                    $arr_submit = array(
                                'name'          => 'submit',
                                'value'         => $pagetitle,
                                'class'         => 'btn green'
                            );
                ?>
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Diet Plan<span class="required" aria-required="true"> *</span></label>
                                    &nbsp;&nbsp;&nbsp; <?php if (!empty($appointmentList[0]->dietplan)) {
                                    ?>
                                    <a href="<?php echo base_url();?>appointment/download_diet/<?= $appointmentList[0]->dietplan; ?>" class="btn default btn-xs green" title="Download Diet Plan" alt="Download Diet Plan"><i class="fa fa-download"></i></a>
                                    <?php } ?>

                                    <input type="file" name="dietplan" id="dietplan" class="form-control" >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Workout Plan<span class="required" aria-required="true"> *</span></label>
                                    &nbsp;&nbsp;&nbsp; <?php if (!empty($appointmentList[0]->workoutplan)) {
                                    ?>
                                    <a href="<?php echo base_url();?>appointment/download_workout/<?= $appointmentList[0]->workoutplan; ?>" class="btn default btn-xs green" title="Download Workout Plan" alt="Download Workout Plan"><i class="fa fa-download"></i></a>
                                    <?php } ?> 

                                    <input type="file" name="workoutplan" id="workoutplan" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                              <div class="col-md-offset-3 col-md-9">
                                <?php echo form_submit($arr_submit)?>
                                <a href="<?php echo base_url();?>appointment"><button type="button" class="btn default">Cancel</button></a>
                              </div>
                          <div class="col-md-6"></div>
                        </div>
                      </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection();?>