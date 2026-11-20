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
    <div class="page-bar"> <?= $breadcrumb; ?> </div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="portlet box grey-cascade">
                <div class="portlet-title">
                    <div class="caption">  <?= $pagetitle?> </div>

                </div>
                <div class="portlet-body form">
                    <?php 
                        $attributes = array('class' => 'login-form', 'id' => 'loginform');
                        echo form_open_multipart('language/edit/'.$languageDetails[0]->language_id, $attributes);

                        $language = set_value('language');                
                        $data_language = array(
                            'name'          => 'language',
                            'id'            => 'language',
                            'value'         => $languageDetails[0]->language,
                            'class'         => 'form-control',
                            'required'      => 'required',
                            'placeholder'   => 'Language Name'
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
                                    <label class="control-label" for="language">Language Name<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_language); ?>
                                    <span class="help-block help-block-error" for="language"><?= $validation->showError('language') ?></span> 
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">

                                    <label class="control-label">Upload Image<span class="required" aria-required="true"> *</span></label>
                                    <input type="file" accept="image/jpg,image/png,image/jpeg" name="language_image" id="language_image" class="form-control" onchange="valimage()">
                                </div>
                            </div>
                            
                            
                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <?php echo form_submit($arr_submit)?>
                                <a href="<?php echo base_url();?>language/"><button type="button" class="btn default">Cancel</button></a>
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