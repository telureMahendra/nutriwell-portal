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
    <?php 
        $attributes = array('class' => 'login-form', 'id' => 'loginform');
                
?>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="portlet box grey-cascade">
                <div class="portlet-title">
                    <div class="caption">
                        <?= $pagetitle?>
                    </div>

                </div>
                <div class="portlet-body form">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Plan Name<span class="required" aria-required="true"> *</span></label>
                                    <span class="form-control form-control-view"><?= $planDetails[0]->name ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Price<span class="required" aria-required="true"> *</span></label>
                                   <span class="form-control form-control-view"><?= $planDetails[0]->price ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Caption<span class="required" aria-required="true"> *</span></label>
                                    <span class="form-control form-control-view"><?= $planDetails[0]->caption ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discription<span class="required" aria-required="true"> *</span></label>
                                    <span class="form-control form-control-view"><?=$planDetails[0]->description ?></span>                                
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Validaty<span class="required" aria-required="true"> *</span></label>
                                    <span class="form-control form-control-view"><?= $planDetails[0]->validaty ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Category<span class="required" aria-required="true"> *</span></label>
                                    <span class="form-control form-control-view"><?= $planDetails[0]->category ?></span>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Background Image<span class="required" aria-required="true"> *</span></label>
                                    <div>
                                        <img src="<?= base_url().'public/uploads/plan/background/'.$planDetails[0]->background_img;?>" alt="Background Image" height="70" width="100">
                                  </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Header Image<span class="required" aria-required="true"> *</span></label>
                                    <div>
                                        <img src="<?= base_url().'public/uploads/plan/header/'.$planDetails[0]->header_img; ?>" alt="Header Image" height="70" width="100">
                                  </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
  <div class="col-md-12"> 
    <!-- BEGIN EXAMPLE TABLE PORTLET-->
    <div class="portlet box grey-cascade">
      <div class="portlet-title">
        <div class="caption"> Manage Features</div>
        <div class="tools"> <a href="javascript:;" class="collapse"> </a></div>
      </div>
      <div class="portlet-body">
        <table class="table table-striped table-bordered table-hover" >
          <thead>
            <tr>
          <th width="20%"> Name</th>  
          <th width="15%"> Image</th>
          <th width="25%"> Caption</th>

          <th width="10%"> Action</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php  
             foreach ($featureDetails as $key=>$feature_details) {

                // echo "<pre>";print_r($feature_details->feature_name);die();
            ?>


           <tr class="gradeX short odd" id="tr_<?php echo $key;?>">
        
                <td width="15%">
                    <input type="hidden" class="form-control" id="rownums" name="rownums" value= "1" required="required" />
                    <span class="form-control form-control-view"><?php echo $feature_details->feature_name; ?></span>
                   
                </td>
                <td width="25%"> 
                <div>
                     <img src="<?= base_url().'public/uploads/plan/feature/'.$feature_details->feature_image; ?>" alt="Header Image" height="70" width="100">
                </div>    
                </td>
                <td width="15%"> 
                    <span class="form-control form-control-view">
                        <?php echo $feature_details->feature_caption; ?>
                    </span>
                </td>
                <td width="10%"></td>
            </tr> 
            <?php } ?>
        </tbody>
        </table>
      </div>
    </div>
    <!-- END EXAMPLE TABLE PORTLET--> 
  </div>
</div>
<div class="row">
    <div class="col-md-12">
          <div class="portlet-body form">
            <div class="form-actions right">
            <div class="row">
             <a href="<?php echo base_url();?>plan">
              <button type="button" class="btn default">Cancel</button>
              </a> </div>
              <div class="col-md-6"> </div>
            </div>
            </div>
        </div>




<?= $this->endSection();?>

