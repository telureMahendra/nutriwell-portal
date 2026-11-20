<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>

<div class="content">
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bordered" style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="portlet-title" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border: none;">
                    <div class="caption">
                        <i class="fa fa-plus-circle" style="color: white; font-size: 20px;"></i>
                        <span class="caption-subject font-white bold uppercase" style="font-size: 18px;"><?= $pagetitle ?></span>
                    </div>
                </div>
                
                <div class="portlet-body form" style="padding: 30px;">
                    <?php 
                    $attributes = array('class' => 'horizontal-form', 'id' => 'subscriptionForm');
                    echo form_open('clients/add_subscription', $attributes);
                    ?>
                    
                    <div class="form-body">
                        <h3 class="form-section" style="margin-top: 0; color: #444; border-bottom: 2px solid #f1f1f1; padding-bottom: 10px;">Client & Plan Information</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group <?= $validation->hasError('client_id') ? 'has-error' : '' ?>">
                                    <label class="control-label bold">Select Client <span class="required">*</span></label>
                                    <?= form_dropdown('client_id', $AllClients, $selected_client_id, 'class="form-control select2me" id="client_id"'); ?>
                                    <span class="help-block text-danger"><?= $validation->getError('client_id') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group <?= $validation->hasError('plan_id') ? 'has-error' : '' ?>">
                                    <label class="control-label bold">Select Subscription Plan <span class="required">*</span></label>
                                    <?= form_dropdown('plan_id', $Allplans, '', 'class="form-control select2me bookinginfo" id="plan_id"'); ?>
                                    <span class="help-block text-danger"><?= $validation->getError('plan_id') ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label bold">Language</label>
                                    <?= form_dropdown('language_id', $Alllanguages, '', 'class="form-control select2me" id="language_id"'); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label bold">City</label>
                                    <?= form_dropdown('city_id', $Allcitys, '', 'class="form-control select2me" id="city_id"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label bold">Start Date <span class="required">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                        </div>

                        <h3 class="form-section" style="margin-top: 30px; color: #444; border-bottom: 2px solid #f1f1f1; padding-bottom: 10px;">Payment & Billing</h3>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label bold">Plan Duration</label>
                                    <input type="text" name="plan_duration" id="plan_duration" class="form-control" placeholder="Calculated from plan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label bold">Sub Total (₹)</label>
                                    <input type="number" name="subtotal" id="subtotal" class="form-control" readonly step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label bold">Coupon Code</label>
                                    <?= form_dropdown('coupon_id', $allCoupon, '', 'class="form-control select2me bookinginfo" id="coupon_id"'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label bold">Discount Amount (₹)</label>
                                    <input type="number" name="discount" id="discount" class="form-control" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label bold">Total Amount (₹) <span class="required">*</span></label>
                                    <input type="number" name="total_amount" id="total_amount" class="form-control" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group <?= $validation->hasError('payment_status') ? 'has-error' : '' ?>">
                                    <label class="control-label bold">Payment Status <span class="required">*</span></label>
                                    <select name="payment_status" id="payment_status" class="form-control" required style="border-left: 4px solid #f1c40f;">
                                        <option value="No">Unpaid / Pending</option>
                                        <option value="Yes" <?= set_select('payment_status', 'Yes') ?>>Paid</option>
                                        <option value="Free" <?= set_select('payment_status', 'Free') ?>>Free / Trial</option>
                                    </select>
                                    <span class="help-block text-danger"><?= $validation->getError('payment_status') ?></span>
                                </div>
                            </div>
                            <div class="col-md-3" id="payment_method_wrapper" style="<?= set_value('payment_status') == 'Yes' ? '' : 'display: none;' ?>">
                                <div class="form-group">
                                    <label class="control-label bold">Payment Method <span class="required">*</span></label>
                                    <select name="payment_method" id="payment_method" class="form-control" style="border-left: 4px solid #2ecc71;">
                                        <option value="">Select Method</option>
                                        <option value="Cash" <?= set_select('payment_method', 'Cash') ?>>Cash</option>
                                        <option value="GPay" <?= set_select('payment_method', 'GPay') ?>>GPay</option>
                                        <option value="PhonePe" <?= set_select('payment_method', 'PhonePe') ?>>PhonePe</option>
                                        <option value="Online" <?= set_select('payment_method', 'Online') ?>>Online / Card</option>
                                        <option value="Bank Transfer" <?= set_select('payment_method', 'Bank Transfer') ?>>Bank Transfer</option>
                                        <option value="Other" <?= set_select('payment_method', 'Other') ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions right" style="background: transparent; border-top: 1px solid #f1f1f1; padding-top: 20px; margin-top: 20px;">
                        <a href="<?= base_url('clients') ?>" class="btn default" style="border-radius: 6px; padding: 10px 25px;">Back to Clients</a>
                        <button type="submit" name="submit" value="Add Subscription" class="btn blue-hoki" style="border-radius: 6px; padding: 10px 30px; background: #667eea; border: none; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                            <i class="fa fa-check"></i> Activate Subscription
                        </button>
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
        // Trigger calculation on page load if plan is already selected
        var init_plan_id = $("#plan_id").val();
        if(init_plan_id) {
            console.log("Initial plan detected:", init_plan_id);
            fetchPlanInfo(init_plan_id);
        }

        // Handle Plan Change - using delegation for select2 compatibility
        $(document).on('change', '#plan_id', function(){
            var plan_id = $(this).val();
            console.log("Plan changed to:", plan_id);
            fetchPlanInfo(plan_id);
        });

        // Toggle Payment Method
        $(document).on('change', '#payment_status', function(){
            if($(this).val() == 'Yes') {
                $("#payment_method_wrapper").fadeIn();
            } else {
                $("#payment_method_wrapper").fadeOut();
                $("#payment_method").val('');
            }
        });

        function fetchPlanInfo(plan_id) {
            if(!plan_id) {
                $('#plan_duration').val('');
                $('#subtotal').val('');
                $('#discount').val(0);
                $('#total_amount').val(0);
                return;
            }

            var apiUrl = '<?= base_url('clients/plan_info') ?>';
            console.log("Fetching plan info from:", apiUrl, "with ID:", plan_id);
            $.ajax({
                url: apiUrl,
                type: 'POST',
                data: { plan_id: plan_id },
                dataType: "json",
                success: function(response){
                    console.log("Plan Info Response:", response);
                    if(response.outcome == 1){                   
                        $('#plan_duration').val(response.plan_duration);
                        $('#subtotal').val(response.price);
                        
                        // Re-calculate with coupon if selected
                        calculateFinal();
                    } else {
                        console.error("Plan Info failed:", response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error (Plan Info):", status, error, xhr.responseText);
                }
            });
        }

        // Handle Coupon Change
        $(document).on('change', '#coupon_id', function(){
            console.log("Coupon changed:", $(this).val());
            calculateFinal();
        });

        function calculateFinal() {
            var coupon_id = $('#coupon_id').val();
            var subtotal = parseFloat($('#subtotal').val()) || 0;
            
            console.log("Calculating Final. Subtotal:", subtotal, "Coupon:", coupon_id);

            if (coupon_id && subtotal > 0) {
                var couponUrl = '<?= base_url('clients/apply_coupon') ?>';
                $.ajax({
                    url : couponUrl,
                    type: 'POST',
                    data: { coupon_id: coupon_id },
                    dataType: "json",
                    success: function(response){
                        console.log("Coupon Response:", response);
                        if(response.outcome == 1){
                            var discountPerc = parseFloat(response.discount) || 0; 
                            var discountAmt = Math.round(((subtotal / 100) * discountPerc) * 100) / 100;               
                            var totalAmt = Math.round((subtotal - discountAmt) * 100) / 100;   

                            $('#discount').val(discountAmt);
                            $('#total_amount').val(totalAmt);
                        } else {
                            $('#discount').val(0);
                            $('#total_amount').val(subtotal);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error (Coupon):", status, error);
                        $('#discount').val(0);
                        $('#total_amount').val(subtotal);
                    }
                });
            } else {
                $('#discount').val(0);
                $('#total_amount').val(subtotal);
            }
        }
    });
</script>

<?= $this->endSection(); ?>
