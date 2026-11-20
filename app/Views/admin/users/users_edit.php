<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation = \Config\Services::validation(); ?>

<style type="text/css">
    span.help-block {
        color: #f30;
    }
</style>

<div class="content">
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
                    <!-- BEGIN FORM -->
                    <?php
                    // First, check if userDetails exists and is not empty
                    if (empty($userDetails)) {
                        echo '<div class="alert alert-danger">User not found!</div>';
                        echo '<a href="' . base_url() . 'users" class="btn btn-default">Back to Users</a>';
                        return;
                    }
                    
                    $attributes = array('class' => 'login-form', 'id' => 'loginform');
                    echo form_open_multipart('users/edit/' . $userDetails->uid, $attributes);
                    
                    $username = set_value('username');
                    $data_username = array(
                        'name' => 'username',
                        'id' => 'username',
                        'value' => $userDetails->username,
                        'class' => 'form-control',
                        'maxlength' => '12',
                        'placeholder' => 'User Name',
                        'readonly' => 'true'
                    );
                    
                    $pass = set_value('pass');
                    $data_pass = array(
                        'name' => 'pass',
                        'id' => 'pass',
                        'type' => 'password',
                        'value' => '',
                        'class' => 'form-control',
                        'maxlength' => '10',
                        'min_length' => '4',
                        'placeholder' => 'Password'
                    );
                    
                    $first_name = set_value('first_name');
                    $data_first_name = array(
                        'name' => 'first_name',
                        'id' => 'first_name',
                        'value' => $userDetails->first_name,
                        'maxlength' => '15',
                        'class' => 'form-control',
                        'required' => 'required',
                        'placeholder' => 'First Name'
                    );
                    
                    $last_name = set_value('last_name');
                    $data_last_name = array(
                        'name' => 'last_name',
                        'id' => 'last_name',
                        'value' => $userDetails->last_name,
                        'class' => 'form-control',
                        'required' => 'required',
                        'maxlength' => '15',
                        'placeholder' => 'Last Name'
                    );
                    
                    $address = set_value('address');
                    $data_address = array(
                        'name' => 'address',
                        'id' => 'address',
                        'value' => $userDetails->address,
                        'class' => 'form-control',
                        'required' => 'required',
                        'rows' => 3,
                        'placeholder' => 'Address'
                    );
                    
                    $email = set_value('email');
                    $data_email = array(
                        'name' => 'email',
                        'id' => 'email',
                        'value' => $userDetails->email,
                        'class' => 'form-control',
                        'required' => 'required',
                        'maxlength' => '100',
                        'placeholder' => 'Email Id'
                    );
                    
                    $phone_number = set_value('phone_number');
                    $data_phone_number = array(
                        'name' => 'phone_number',
                        'id' => 'phone_number',
                        'value' => $userDetails->phone_number,
                        'class' => 'form-control',
                        'required' => 'required',
                        'maxlength' => '13',
                        'placeholder' => 'Phone Number'
                    );
                    
                    $data_user_role = array();
                    $data_user_role[''] = 'Select Role';
                    $data_user_role['Admin'] = 'Admin';
                    $data_user_role['User'] = 'User';
                    $data_user_role['SalesAgent'] = 'SalesAgent';
                    $data_user_role['tele_caller'] = 'Tele Caller';
                    $data_user_role['sales_manager'] = 'Sales Manager';
                    $data_user_role['sales_executive'] = 'Sales Executive';
                    
                    $user_role = set_value('user_role');
                    
                    $arr_submit = array(
                        'name' => 'submit',
                        'value' => $pagetitle,
                        'class' => 'btn green'
                    );
                    ?>
                    
                    <div class="form-body">
                        <!-- Success/Error Messages -->
                        <?php if (session()->has('suc_msg')): ?>
                            <div class="alert alert-<?= session('msg-type') ?> alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                                <?= session('suc_msg') ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Validation Errors -->
                        <?php if ($validation->getErrors()): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($validation->getErrors() as $error): ?>
                                    <p><?= $error ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">User Name</label>
                                    <?= form_input($data_username); ?>
                                    <span class="help-block help-block-error" for="username"><?= $validation->showError('username') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Password<small class="required" aria-required="true">(If you want to change then enter)</small></label>
                                    <?= form_input($data_pass); ?>
                                    <span class="help-block help-block-error" for="pass"><?= $validation->showError('pass') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">First Name<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_first_name); ?>
                                    <span class="help-block help-block-error" for="first_name"><?= $validation->showError('first_name') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Last Name<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_last_name); ?>
                                    <span class="help-block help-block-error" for="last_name"><?= $validation->showError('last_name') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Email<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_email); ?>
                                    <span class="help-block help-block-error" for="email"><?= $validation->showError('email') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Phone Number<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_phone_number); ?>
                                    <span class="help-block help-block-error" for="phone_number"><?= $validation->showError('phone_number') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">User Role<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('user_role', $data_user_role, $userDetails->user_role, 'class="select2_category form-control" id="user_role" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="user_role"><?= $validation->showError('user_role') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">State<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('state_id', $AllState, $userDetails->state_id, 'class="select2_category form-control" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="state_id"><?= $validation->showError('state_id') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Address<span class="required" aria-required="true"> *</span></label>
                                    <?= form_textarea($data_address); ?>
                                    <span class="help-block help-block-error" for="address"><?= $validation->showError('address') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions right">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <?= form_submit($arr_submit) ?>
                                <a href="<?= base_url(); ?>users">
                                    <button type="button" class="btn default">Cancel</button>
                                </a>
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

<?= $this->endSection(); ?>