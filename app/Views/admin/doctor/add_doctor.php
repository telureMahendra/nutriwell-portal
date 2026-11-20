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
                        echo form_open_multipart('doctor/add', $attributes);

                        $username = set_value('username');                
                        $data_username = array(
                            'name'          => 'username',
                            'id'            => 'username',
                            'value'          => $username,
                            'class'         => 'form-control',
                            'required'      => 'required',
                            'maxlength'     => '12',
                            'placeholder'   => 'User Name'
                        );

                        $pass = set_value('pass');                
                        $data_pass = array(
                            'name'          => 'pass',
                            'id'            => 'pass',
                            'type'          => 'password',
                            'value'         => '',
                            'class'         => 'form-control',
                            'required'      => 'required',
                            'maxlength'     => '10',
                            'min_length'    => '4',
                            'placeholder'   => 'Password'
                        );

                        $doctor_name = set_value('doctor_name');                
                        $data_doctor_name = array(
                            'name'          => 'doctor_name',
                            'id'            => 'doctor_name',
                            'value'          => $doctor_name,
                            'class'         => 'form-control',
                            'required'      => 'required',
                            'placeholder'   => 'Mentor Name'
                        );

                        $phone_number = set_value('phone_number');                
                        $data_phone_number = array(
                            'name'          => 'phone_number',
                            'id'            => 'phone_number',
                            'value'         => $phone_number,
                            'class'         => 'form-control',
                            'type'          => 'number',
                            'required'      => 'required',
                            'maxlength'     => '13',
                            'placeholder'   => 'phone Number'
                        );

                        $email = set_value('email');                 
                        $data_email = array(
                            'name'          => 'email',
                            'id'            => 'email',
                            'value'         => $email,
                            'class'         => 'form-control',
                            'required'      => 'required',
                            'maxlength'     => '100',
                            'placeholder'   => 'Email Id'
                        );
                       
                        $discription = set_value('discription');                  
                        $data_discription = array(
                            'name'          => 'discription',
                            'id'            => 'discription',
                            'value'         => $discription,
                            'class'         => 'form-control',
                            'required'      => 'required',
                            'rows'          => 3,
                            'placeholder'   => 'Discription'
                        );

                        $genderArray = array(
                            '' => 'Select Gender',
                            'Male' => 'Male',
                            'Female' => 'Female'
                        );

                        $years = set_value('years');                
                        $data_years = array(
                            'name'          => 'years',
                            'id'            => 'years',
                            'value'          => $years,
                            'class'         => 'form-control',
                            'type'          => 'number',
                            'required'      => 'required',
                            'placeholder'   => 'Years'
                        );

                        $month = ['' => 'Select Month', '0' => '0', '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05', '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11', '12' => '12'];

                        $qualification = set_value('qualification');                 
                        $data_qualification = array(
                            'name'          => 'qualification',
                            'id'            => 'qualification',
                            'value'         => $qualification,
                            'class'         => 'form-control',
                            'required'      => 'required',
                            'maxlength'     => '500',
                            'placeholder'   => 'Qualification'
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
                                    <label class="control-label" for="username">User Name<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_username); ?>
                                    <span class="help-block help-block-error" for="username"><?= $validation->showError('username') ?></span> 
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="pass">Password<span class="required" aria-required="true"> *</span></label>
                                    <?php echo form_input($data_pass); ?>
                                    <span class="help-block help-block-error" for="pass"><?= $validation->showError('pass') ?></span> 
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="doctor_name">Mentor Name<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_doctor_name); ?>
                                    <span class="help-block help-block-error" for="doctor_name"><?= $validation->showError('doctor_name') ?></span> 
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="doctorimage">Upload Image<span class="required" aria-required="true"> *</span></label>
                                    <input type="file" accept="image/jpg,image/png,image/jpeg" name="doctorimage" id="doctorimage" class="form-control" onchange="valimage()">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="gender">Gender<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('gender',$genderArray,'','class="select2_category form-control" id="gender" tabindex="0"');?>
                                    <span class="help-block help-block-error" for="gender"><?= $validation->showError('gender') ?></span>                                
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="phone_number">Phone Number<span class="required" aria-required="true"> *</span></label>
                                    <?php echo form_input($data_phone_number); ?>
                                    <span class="help-block help-block-error" for="phone_number"><?= $validation->showError('phone_number') ?></span>                                
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="email">Email<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_email); ?>
                                    <span class="help-block help-block-error" for="email"><?= $validation->showError('email') ?></span>                                
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="designation"> Designation <span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('designation',$allDesignation,'','class="select2_category form-control" id="designation" tabindex="0" required="required" ');?>
                                    <span class="help-block help-block-error" for="designation"><?= $validation->showError('designation') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                        <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="qualification">Qualification<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_qualification); ?>
                                    <span class="help-block help-block-error" for="qualification"><?= $validation->showError('qualification') ?></span>                                
                                </div>
                            </div>                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label"> Work Experiance (Years) <span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_years); ?>
                                    <span class="help-block help-block-error" for="years"><?= $validation->showError('years') ?></span>                                
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="month"> Work Experiance (Month) <span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('month',$month,'','class="select2_category form-control" id="month" tabindex="0" required="required"');?>
                                    <span class="help-block help-block-error" for="month"><?= $validation->showError('month') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="state_id"> State <span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('state_id',$stateList,'','class="select2_category form-control" id="state_id" tabindex="0" required="required"');?>
                                    <span class="help-block help-block-error" for="state_id"><?= $validation->showError('state_id') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="city_id"> City <span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('city_id',$cityList,'','class="select2_category form-control" id="city_id" tabindex="0" required="required"');?>
                                    <span class="help-block help-block-error" for="city_id"><?= $validation->showError('city_id') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            
                        
                       
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Known Languages <span class="required" aria-required="true"> *</span></label>
                                    <?php
                                        foreach ($languageList as $language_id => $language) {
                                            echo '<div class="checkbox">';
                                            echo '<label>';
                                            echo '<input type="checkbox" name="language_id[]" value="' . $language_id . '">';
                                            echo $language;
                                            echo '</label>';
                                            echo '</div>';
                                        }
                                    ?>
                                    <span class="help-block help-block-error" for="language_id"><?= $validation->showError('language_id') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discription<span class="required" aria-required="true"> *</span></label>
                                    <?= form_textarea($data_discription); ?>
                                    <span class="help-block help-block-error" for="discription"><?= $validation->showError('discription') ?></span>                                
                                </div>
                            </div>                                

                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <?php echo form_submit($arr_submit)?>
                                <a href="<?php echo base_url();?>doctor"><button type="button" class="btn default">Cancel</button></a>
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
    var fileInput = document.getElementById('doctorimage');
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
