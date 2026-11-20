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
                    echo form_open_multipart('plan/edit/'.$planDetails[0]->plan_id, $attributes);
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
                  <?php  
                    $planname = set_value('planname');                
                    $data_plan = array(
                        'name'          => 'planname',
                        'id'            => 'planname',
                        'value'          =>$planDetails[0]->name ,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'placeholder'   => 'Plan Name'
                    );
                    
                    $caption = set_value('caption');                  
                    $data_caption = array(
                        'name'          => 'caption',
                        'id'            => 'caption',
                        'value'         => $planDetails[0]->caption ,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'rows'          => 3,
                        'placeholder'   => 'Caption'
                    );

                    $discription = set_value('discription');                  
                    $data_description = array(
                        'name'          => 'discription',
                        'id'            => 'discription',
                        'value'         => $planDetails[0]->description ,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'rows'          => 3,
                        'placeholder'   => 'Discription'
                    );
                    $price = set_value('price');                
                    $data_price = array(
                        'name'          => 'price',
                        'id'            => 'price',
                        'type'          => 'number',
                        'value'          => $planDetails[0]->price,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'placeholder'   => 'Price'
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
                                    <label class="control-label">Plan Name<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_plan); ?>
                                    <span class="help-block help-block-error" for="title"><?= $validation->showError('title') ?></span> 
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Price<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_price); ?>
                                    <span class="help-block help-block-error" for="title"><?= $validation->showError('title') ?></span> 
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Caption<span class="required" aria-required="true"> *</span></label>
                                    <?= form_textarea($data_caption); ?>
                                    <span class="help-block help-block-error" for="description"><?= $validation->showError('description') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discription<span class="required" aria-required="true"> *</span></label>
                                    <?= form_textarea($data_description); ?>
                                    <span class="help-block help-block-error" for="description"><?= $validation->showError('description') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Validaty<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('validaty',$validaty,$planDetails[0]->validaty,'class="select2me form-control" id="validaty" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="dropdown"><?= $validation->showError('select') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Category<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('category',$category,$planDetails[0]->category,'class="select2me form-control" id="category" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="dropdown"><?= $validation->showError('select') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Background Image<span class="required" aria-required="true"> *</span></label>
                                    <img src="<?= base_url().'public/uploads/plan/background/'.$planDetails[0]->background_img;?>" alt="Background Image" height="70" width="100">
                                    <input type="file" accept="image/jpg,image/png,image/jpeg" name="background_img" value="<?= base_url();?>public/uploads/plan/background/<?= $planDetails[0]->background_img ?>" id="background_img" class="form-control" onchange="valimage1()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Header Image<span class="required" aria-required="true"> *</span></label>
                                    <img src="<?= base_url().'public/uploads/plan/header/'.$planDetails[0]->header_img; ?>" alt="Header Image" height="70" width="100">
                                    <input type="file" accept="image/jpg,image/png,image/jpeg" name="header_img" id="header_img" class="form-control" onchange="valimage2()">
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
        <div class="table-toolbar">
                <div class="row">
                  <div class="col-md-6">
                    <div class="btn-group">
                      <div class="btn green add_new_row">
                        Add New Feature <i class="fa fa-plus"></i>
                      </div>
                    </div>
                  </div>
                  
                </div>
              </div>
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
            ?>
            <tr class="gradeX short odd" id="tr_<?php echo $key;?>">
        
                <td width="15%">
                     <input type="hidden" class="form-control" id="rownums" name="rownums" value= "1" required="required" />
                    <input type="hidden" class="form-control" id="pf_id" name="pf_id[]" value= "<?= $feature_details->pf_id?>" required="required" />
                    <input type="text" name="feature_name[]" value="<?php echo $feature_details->feature_name; ?>" class="form-control" placeholder="Feature Name" required="required">
                </td>
                <td width="25%">  
                    <img src="<?= base_url().'public/uploads/plan/feature/'.$feature_details->feature_image; ?>" alt="Header Image" height="70" width="100"> 

                    <input type="file" accept="image/jpg,image/png,image/jpeg" name="feature_img[]" id="feature_img" class="form-control" value="<?php echo $feature_details->feature_image; ?>" onchange="valimage3()"> 
                    <input type="text" name="old_img-<?php echo $key;?>" value="<?= $feature_details->feature_image; ?>" hidden>
                </td>
                <td width="15%"> 

                    <textarea name="feature_caption[]" class="form-control" ><?php echo $feature_details->feature_caption; ?></textarea>
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
              <div class="col-md-offset-3 col-md-9"> <?php echo form_submit($arr_submit)?> <a href="<?php echo base_url();?>plan">
              <button type="button" class="btn default">Cancel</button>
              </a> </div>
              <div class="col-md-6"> </div>
            </div>
            </div>
        </div>
 <?= form_close(); ?>

<script type="text/javascript">
    function valimage1() {
        var fileInput1 = document.getElementById('background_img');
        var filePath1 = fileInput1.value;
        var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

        if (!allowedExtensions.exec(filePath1)) {
            alert('Invalid file type. Please upload an image with extension .jpg, .jpeg, or .png.');
            fileInput1.value = '';
            return false;
        }
    }

</script>
<script type="text/javascript">
    function valimage2() {
        var fileInput2 = document.getElementById('header_img');
        var filePath2 = fileInput2.value;
        var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

        if (!allowedExtensions.exec(filePath2)) {
            alert('Invalid file type. Please upload an image with extension .jpg, .jpeg, or .png.');
            fileInput2.value = '';
            return false;
        }
    }

</script>
<script type="text/javascript">
    function valimage3() {
        var fileInput3 = document.getElementById('feature_img');
        var filePath3 = fileInput3.value;
        var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

        if (!allowedExtensions.exec(filePath3)) {
            alert('Invalid file type. Please upload an image with extension .jpg, .jpeg, or .png.');
            fileInput3.value = '';
            return false;
        }
    }

</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script type="text/javascript">
    $(function(){
        $(".add_new_row").click(function() {
            var rownums = $('#rownums').val();
            //alert(rownums);
            $.ajax({
                url: '<?= base_url('plan/add_new_row') ?>',
                type: 'POST',
                data: {rownums: rownums},
                async: false,
                success: function(responce){
                    //alert(responce);
                    $('#tableBody').append(responce);
                    rownums = parseInt(rownums) + 1;
                    $('#rownums').val(rownums);
                }
            });
        });
    });
</script>

<?= $this->endSection();?>