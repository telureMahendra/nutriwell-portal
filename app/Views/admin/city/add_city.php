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
                    echo form_open_multipart('city/add', $attributes);

                    $city = set_value('city');                
                    $data_city = array(
                        'name'          => 'city',
                        'id'            => 'city',
                        'value'          => $city,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'placeholder'   => 'City'
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
                                    <label class="control-label">City<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_city); ?>
                                    <span class="help-block help-block-error" for="title"><?= $validation->showError('title') ?></span> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                          <div class="col-md-offset-3 col-md-9">
                            <?php echo form_submit($arr_submit)?>
                            <a href="<?php echo base_url();?>city"><button type="button" class="btn default">Cancel</button></a>
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


<?= $this->endSection();?>
