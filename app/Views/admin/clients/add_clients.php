<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation = \Config\Services::validation(); ?>
<div class="content">
    <style type="text/css">
        span.help-block {
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
                        
                        <?= $pagetitle ?>
                    </div>

                </div>
                <div class="portlet-body form">
                    <?php
                    $attributes = array('class' => 'login-form', 'id' => 'loginform');
                    echo form_open_multipart('clients/add', $attributes);

                    $client = set_value('client_name');
                    $data_client = array(
                        'name' => 'client_name',
                        'id' => 'client_name',
                        'value' => $client,
                        'class' => 'form-control',
                        'required' => 'required',
                        'placeholder' => 'Client Name'
                    );

                    $email = set_value('email');
                    $data_email = array(
                        'name' => 'email',
                        'id' => 'email',
                        'value' => $email,
                        'class' => 'form-control',
                        'required' => 'required',
                        'maxlength' => '100',
                        'placeholder' => 'Email Id'
                    );

                    $phone_number = set_value('phone_number');
                    $data_phone_number = array(
                        'name' => 'phone_number',
                        'id' => 'phone_number',
                        'value' => $phone_number,
                        'class' => 'form-control',
                        'required' => 'required',
                        'maxlength' => '13',
                        'placeholder' => 'Phone Number'
                    );

                    $age = set_value('age');
                    $data_age = array(
                        'name' => 'age',
                        'id' => 'age',
                        'type' => 'number',
                        'value' => $age,
                        'class' => 'form-control',
                        'required' => 'required',
                        'placeholder' => 'Age'
                    );

                    $weight = set_value('weight');
                    $data_weight = array(
                        'name' => 'weight',
                        'id' => 'weight',
                        'type' => 'text',  
                        'pattern' => '\d+(\.\d+)?', 
                        'value' => $weight,
                        'class' => 'form-control',
                        'required' => 'required',
                        'placeholder' => 'Weight'
                    );

                    $height = set_value('height');
                    $data_height = array(
                        'name' => 'height',
                        'id' => 'height',
                        'type' => 'text',  
                        'pattern' => '\d+(\.\d+)?', 
                        'value' => $height,
                        'class' => 'form-control',
                        'required' => 'required',
                        'placeholder' => 'Height'
                    );

                    $data_gender=set_value('client_gender');
                    $data_gender = array(
                        '' => 'Select Gender',
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Others' => 'Others',

                    );
                    
                    $unitList = array(
                        '' => 'Select Unit',
                        'ft' => 'FT',
                        'in' => 'IN',
                        'cm' => 'CM',
                    );
                    $dietList = array(
                        ''        => 'Select Diet',  
                        'Veg'     => 'Veg',
                        'NonVeg'  => 'NonVeg'
                    );
                    $arr_submit = array(
                        'name' => 'submit',
                        'value' => $pagetitle,
                        'class' => 'btn green'
                    );

                    ?>
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Client Name<span class="required" aria-required="true">
                                            *</span></label>
                                    <?= form_input($data_client); ?>
                                    <span class="help-block help-block-error" for="name">
                                        <?= $validation->showError('name') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Email<span class="required" aria-required="true">
                                            *</span></label>
                                    <?= form_input($data_email); ?>
                                    <span class="help-block help-block-error" for="email">
                                        <?= $validation->showError('email') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Phone Number<span class="required"
                                            aria-required="true"> *</span></label>
                                    <?php echo form_input($data_phone_number); ?>
                                    <span class="help-block help-block-error" for="phone_number">
                                        <?= $validation->showError('phone_number') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Gender<span class="required" aria-required="true">
                                            *</span></label>
                                    <?= form_dropdown('client_gender',$data_gender,$client_gender['client_gender'], 'class="select2_category form-control" id="client_gender" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="client_gender">
                                        <?= $validation->showError('client_gender') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Lifestyle<span class="required" aria-required="true">
                                            *</span></label>
                                    <?= form_dropdown('lifestyle', $lifestyleList,$lifestyle, 'class="select2_category form-control" id="lifestyle" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="lifestyle">
                                        <?= $validation->showError('lifestyle') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Goal<span class="required" aria-required="true">
                                            *</span></label>
                                    <?= form_dropdown('goal', $goalList,$goal, 'class="select2_category form-control" id="goal" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="goal">
                                        <?= $validation->showError('goal') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Age<span class="required" aria-required="true">
                                            *</span></label>
                                    <?= form_input($data_age); ?>
                                    <span class="help-block help-block-error" for="age">
                                        <?= $validation->showError('age') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Weight<span class="required" aria-required="true">
                                            *</span></label>
                                    <?= form_input($data_weight); ?>
                                    <span class="help-block help-block-error" for="weight">
                                        <?= $validation->showError('weight') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Diet<span class="required" aria-required="true">*</span></label>
                                       <?= form_dropdown('diet',$dietList,$goal, 'class="select2_category form-control" id="diet" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="diet">
                                        <?= $validation->showError('diet') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Height<span class="required" aria-required="true">*</span></label>
                                        <?= form_input($data_height); ?>
                                    <span class="help-block help-block-error" for="unit">
                                        <?= $validation->showError('height') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Height In<span class="required" aria-required="true">*</span></label>
                                       <?= form_dropdown('heightIn',$unitList,$heightIn, 'class="select2_category form-control" id="heightIn" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="unit">
                                        <?= $validation->showError('heightIn') ?>
                                    </span>
                                </div>
                            </div>

                             <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Upload Image<span class="required"
                                            aria-required="true"> *</span></label>
                                    <input type="file" accept="image/jpg,image/png,image/jpeg" name="clientimage"
                                        id="clientimage" class="form-control" onchange="valimage()">
                                </div>
                            </div>
                        </div>
                        </div>
                    <div class="form-actions right">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <?php echo form_submit($arr_submit) ?>
                                <a href="<?php echo base_url(); ?>clients"><button type="button"
                                        class="btn default">Cancel</button></a>
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
        var fileInput = document.getElementById('clientimage');
        var filePath = fileInput.value;
        var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

        if (!allowedExtensions.exec(filePath)) {
            alert('Invalid file type. Please upload an image with extension .jpg, .jpeg, or .png.');
            fileInput.value = '';
            return false;
        }
    }

</script>

<?= $this->endSection(); ?>