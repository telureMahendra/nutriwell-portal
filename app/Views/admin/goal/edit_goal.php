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

                </div>
                <div class="portlet-body form">
                    <?php 
                    $attributes = array('class' => 'login-form', 'id' => 'loginform');
                    echo form_open_multipart('goal/edit/'.$goalDetails[0]->goal_id, $attributes);

                    $goal = set_value('goal');                
                    $data_goal = array(
                        'name'          => 'goal',
                        'id'            => 'goal',
                        'value'          => $goalDetails[0]->name,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'placeholder'   => 'Goal'
                    );
                    
                    $description = set_value('description');                  
                    $data_discription = array(
                                'name'          => 'description',
                                'id'            => 'discription',
                                'value'         => $goalDetails[0]->description,
                                'class'         => 'form-control',
                                'required'      => 'required',
                                'rows'          => 3,
                                'placeholder'   => 'Description'
                            );
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
                                    <label class="control-label">Goal<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_goal); ?>
                                    <span class="help-block help-block-error" for="goal"><?= $validation->showError('goal') ?></span> 
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">

                                    <label class="control-label">Upload Image<span class="required" aria-required="true"> *</span></label>
                                    <input type="file" accept="image/jpg,image/png,image/jpeg" name="goalimage" id="goalimage" class="form-control" onchange="valimage()">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Description<span class="required" aria-required="true"> *</span></label>
                                    <?= form_textarea($data_discription); ?>
                                    <span class="help-block help-block-error" for="description"><?= $validation->showError('description') ?></span>                                
                                </div>
                            </div>
                        </div>
                       
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                          <div class="col-md-offset-3 col-md-9">
                            <?php echo form_submit($arr_submit)?>
                            <a href="<?php echo base_url();?>goal"><button type="button" class="btn default">Cancel</button></a>
                        </div>
                        <div class="col-md-6"> </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
</div> 
<script type="text/javascript">
    function valimage() {
    var fileInput = document.getElementById('goalimage');
    var filePath = fileInput.value;
    var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
    
    if (!allowedExtensions.exec(filePath)) {
        alert('Invalid file type. Please upload an image with extension .jpg, .jpeg, or .png.');
        fileInput.value = '';
        return false;
    }
}

</script>
 
<?= $this->endSection();?>