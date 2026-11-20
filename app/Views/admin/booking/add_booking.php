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
                    echo form_open_multipart('booking/add', $attributes);

                    $plan_duration = set_value('plan_duration');                
                    $data_plan_duration = array(
                        'name'          => 'plan_duration',
                        'id'            => 'plan_duration',
                        'value'          => $plan_duration,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'placeholder'   => 'Plan Duration'
                    );

                    $subtotal = set_value('subtotal');                
                    $data_subtotal = array(
                        'name'          => 'subtotal',
                        'id'            => 'subtotal',
                        'value'          => $subtotal,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'placeholder'   => 'Sub Total'
                    );

                    $coupon_id = set_value('coupon_id');                
                    $data_coupon_id = array(
                        'name'          => 'coupon_id',
                        'id'            => 'coupon_id',
                        'value'          => $coupon_id,
                        'class'         => 'form-control bookinginfo',
                        'required'      => 'required',
                        'placeholder'   => 'Coupon Code'
                    );

                    $discount = set_value('discount');                
                    $data_discount = array(
                        'name'          => 'discount',
                        'id'            => 'discount',
                        'value'          => $discount,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'placeholder'   => 'Discount'
                    );

                    $total_amount = set_value('total_amount');                
                    $data_total_amount = array(
                        'name'          => 'total_amount',
                        'id'            => 'total_amount',
                        'value'          => $total_amount,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'placeholder'   => 'Total Amount'
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
                                    <label class="control-label">Client Name<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('client_id',$AllClients,'','class="select2me form-control" id="client_id" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="dropdown"><?= $validation->showError('select') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Language<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('language_id',$Alllanguages,'','class="select2me form-control" id="language_id" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="dropdown"><?= $validation->showError('select') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">City Name<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('city_id',$Allcitys,'','class="select2me form-control" id="city_id" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="dropdown"><?= $validation->showError('select') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Plan Name<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('plan_id',$Allplans,'','class="select2me form-control bookinginfo" id="plan_id" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="dropdown"><?= $validation->showError('select') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Plan Duration<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_plan_duration); ?>
                                    <span class="help-block help-block-error" for="plan_duration"><?= $validation->showError('plan_duration') ?></span> 
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Sub Total <span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_subtotal); ?>
                                    <span class="help-block help-block-error" for="subtotal"><?= $validation->showError('subtotal') ?></span> 
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Coupon Code<span class="required" aria-required="true"> *</span></label>
                                    <?= form_dropdown('coupon_id',$allCoupon,'','class="select2me form-control bookinginfo" id="coupon_id" tabindex="0"'); ?>
                                    <span class="help-block help-block-error" for="dropdown"><?= $validation->showError('select') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discount Amount<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_discount); ?>
                                    <span class="help-block help-block-error" for="discount"><?= $validation->showError('discount') ?></span> 
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Total Amount<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_total_amount); ?>
                                    <span class="help-block help-block-error" for="total_amount"><?= $validation->showError('total_amount') ?></span> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <?php echo form_submit($arr_submit)?>
                                <a href="<?php echo base_url();?>booking"><button type="button" class="btn default">Cancel</button></a>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>

<script type="text/javascript">
    $(function(){
        $(".bookinginfo").change(function(){
            var plan_id = $('#plan_id').val();

            $.ajax({
                url: '<?= base_url('booking/plan_info') ?>', // Generate the URL using site_url()
                type: 'POST',
                data: { plan_id: plan_id },
                dataType: "json",
                success: function(response){
                    var outcome = response.outcome;
                    if(outcome == 1){                   
                        var plan_duration = response.plan_duration; 
                        var price = response.price; 
                        $('#plan_duration').val(plan_duration);
                        $('#subtotal').val(price);
                        $('#discount').val('0');
                        $('#total_amount').val(price);
                    } else {
                        $('#plan_duration').val('');
                        $('#subtotal').val('');
                        $('#discount').val('');
                        $('#total_amount').val('');
                        alert('Something went wrong with your request');
                    }
                }
            });
        });
        $(".bookinginfo").change(function(){
            var coupon_id = $('#coupon_id').val();
            if (coupon_id!='') {
                $.ajax({
                    url : '<?= base_url('booking/apply_coupon') ?>',
                    type: 'POST',
                    data: { coupon_id:coupon_id },
                    dataType: "json",
                    success: function(response){
                        if(response.outcome == 1){
                            var discount = response.discount; 
                            var subtotal = $('#subtotal').val();
                            var discountAmt = Math.round((subtotal/100)*discount,2);               
                            var totalAmt =  Math.round(subtotal-discountAmt,2);   

                            $('#discount').val(discountAmt);
                            $('#total_amount').val(totalAmt);
                        } else {
                            $('#discount').val('');
                            $('#total_amount').val('');
                            alert('Something went wrong with your request');
                        }
                    }
                });
            }
        });
    });
</script>


<?= $this->endSection();?>
