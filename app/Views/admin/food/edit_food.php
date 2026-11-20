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
                    <div class="caption">  <?= $pagetitle?> </div>

                </div>
                <div class="portlet-body form">
                <?php 
                    $attributes = array('class' => 'login-form', 'id' => 'loginform');
                    echo form_open_multipart('food/edit/'.$foodTypeDetails[0]->food_id, $attributes);

                    $food_name = set_value('food_name');                
                    $data_food = array(
                                'name'          => 'food_name',
                                'id'            => 'food_name',
                                'value'          => $foodTypeDetails[0]->food_name,
                                'class'         => 'form-control',
                                'required'      => 'required',
                                'placeholder'   => 'Food'
                            );
                            $data_calories = array(
                                'name'          => 'calories',
                                'id'            => 'calories',
                                'value'          => $foodTypeDetails[0]->calories,
                                'class'         => 'form-control',
                                'required'      => 'required',
                                'placeholder'   => 'Calories'
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
                                    <label class="control-label">Food<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_food); ?>
                                    <span class="help-block help-block-error" for="title"><?= $validation->showError('food_name') ?></span> 
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Calories<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_calories); ?>
                                    <span class="help-block help-block-error" for="title"><?= $validation->showError('calories') ?></span> 
                                </div>
                            </div>                            

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Measurement Unit</label>
                                    <select name="unit" class="form-control">
                                        <?= foodUnitOptions($foodTypeDetails[0]->unit ?? null); ?>
                                    </select>
                                    <span class="help-block" style="color:#888;">
                                        <?php if (empty($foodTypeDetails[0]->unit)) { ?>
                                            Not set - the app currently shows
                                            <strong><?= foodUnitEmoji(deriveFoodUnitFromName($foodTypeDetails[0]->food_name)); ?>
                                            <?= foodUnitLabel(deriveFoodUnitFromName($foodTypeDetails[0]->food_name)); ?></strong>,
                                            worked out from the food name.
                                        <?php } else { ?>
                                            Leave it unset to fall back to working the unit out from the food name.
                                        <?php } ?>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Food Type<span class="required" aria-required="true"> *</span></label>
                                    <select name="food_type_id[]" multiple="multiple" class="form-control">
                                        <?php echo $food_type_option;?>
                                    </select>
                                    <span class="help-block help-block-error" for="title"><?= $validation->showError('food_type_id') ?></span>
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                              <div class="col-md-offset-3 col-md-9">
                                <?php echo form_submit($arr_submit)?>
                                <a href="<?php echo base_url();?>food/edit/<?= $foodTypeDetails[0]->food_id; ?>"><button type="button" class="btn default">Cancel</button></a>
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