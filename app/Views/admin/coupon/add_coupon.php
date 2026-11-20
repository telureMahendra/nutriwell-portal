<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>
<div class="content">
    <style type="text/css">
        span.help-block{
            color: #f30;
        }
        .help-text {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
            display: block;
        }
        .input-group-addon {
            background-color: #f5f5f5;
            border: 1px solid #c2cad8;
            padding: 6px 12px;
            font-size: 14px;
            font-weight: normal;
            line-height: 1;
            color: #555;
            text-align: center;
            border-radius: 4px;
            border-right: 0;
        }
        .input-group .form-control:first-child {
            border-left: 0;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .input-group-addon:first-child {
            border-right: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
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
                    echo form_open_multipart('coupon/add', $attributes);

                    // Get form values (if validation fails)
                    $coupon_code = set_value('coupon_code');                
                    $data_coupon_code = array(
                        'name'          => 'coupon_code',
                        'id'            => 'coupon_code',
                        'value'         => $coupon_code,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'placeholder'   => 'Enter coupon code (e.g., WELCOME10)'
                    );
                    
                    // NEW: Add discount type selection
                    $discount_type = set_value('discount_type', 'fixed');
                    
                    $discount = set_value('discount');                  
                    $data_discount = array(
                        'name'          => 'discount',
                        'id'            => 'discount',
                        'type'          => 'number',
                        'value'         => $discount,
                        'class'         => 'form-control discount-input',
                        'required'      => 'required',
                        'step'          => '0.01',
                        'min'           => '0',
                        'placeholder'   => 'Enter discount value'
                    );
                    
                    // NEW: Minimum order amount field
                    $min_order_amount = set_value('min_order_amount');                  
                    $data_min_order_amount = array(
                        'name'          => 'min_order_amount',
                        'id'            => 'min_order_amount',
                        'type'          => 'number',
                        'value'         => $min_order_amount,
                        'class'         => 'form-control',
                        'step'          => '0.01',
                        'min'           => '0',
                        'placeholder'   => 'Enter minimum order amount'
                    );
                    
                    // Validity Dates
                    $valid_from = set_value('valid_from', date('Y-m-d'));                  
                    $data_valid_from = array(
                        'name'          => 'valid_from',
                        'id'            => 'valid_from',
                        'type'          => 'date',
                        'value'         => $valid_from,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'min'           => date('Y-m-d')
                    );
                    
                    $valid_to = set_value('valid_to', date('Y-m-d', strtotime('+30 days')));                  
                    $data_valid_to = array(
                        'name'          => 'valid_to',
                        'id'            => 'valid_to',
                        'type'          => 'date',
                        'value'         => $valid_to,
                        'class'         => 'form-control',
                        'min'           => date('Y-m-d'),
                        'placeholder'   => 'Leave empty for no expiry'
                    );
                    
                    // Usage Limits
                    $usage_limit = set_value('usage_limit');                  
                    $data_usage_limit = array(
                        'name'          => 'usage_limit',
                        'id'            => 'usage_limit',
                        'type'          => 'number',
                        'value'         => $usage_limit,
                        'class'         => 'form-control',
                        'min'           => '1',
                        'placeholder'   => 'Leave empty for unlimited usage'
                    );
                    
                    $max_usage_per_client = set_value('max_usage_per_client', 1);                  
                    $data_max_usage_per_client = array(
                        'name'          => 'max_usage_per_client',
                        'id'            => 'max_usage_per_client',
                        'type'          => 'number',
                        'value'         => $max_usage_per_client,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'min'           => '1',
                        'placeholder'   => 'How many times a single client can use this'
                    );
                    
                    // Description
                    $description = set_value('description');                  
                    $data_description = array(
                        'name'          => 'description',
                        'id'            => 'description',
                        'value'         => $description,
                        'class'         => 'form-control',
                        'rows'          => '3',
                        'placeholder'   => 'Optional description (visible to admin only)'
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
                                    <label class="control-label">Coupon Code<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_coupon_code); ?>
                                    <span class="help-block help-block-error" for="coupon_code"><?= $validation->showError('coupon_code') ?></span>
                                    <span class="help-text">Unique code that clients will enter (e.g., SUMMER2024)</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Coupon Type<span class="required" aria-required="true"> *</span></label>
                                    <select name="coupon_type" id="coupon_type_select" class="form-control" required>
                                        <option value="general">General (available to all clients)</option>
                                        <option value="user_specific">User Specific (assign to selected clients)</option>
                                    </select>
                                    <span class="help-text">General coupons can be used by anyone; user-specific are locked to assigned clients</span>
                                </div>
                            </div>
                        </div>

                        <!-- User-specific client picker (hidden by default) -->
                        <div class="row" id="user_specific_section" style="display:none;">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Assign to Clients<span class="required" aria-required="true"> *</span></label>
                                    <div class="client-picker-wrap">
                                        <input type="text" id="client_search_input" class="form-control" placeholder="Search client by name, email or phone…" autocomplete="off">
                                        <div id="client_search_results" class="client-search-dropdown" style="display:none;"></div>
                                    </div>
                                    <div id="selected_clients_tags" class="selected-clients-tags"></div>
                                    <input type="hidden" name="assigned_client_ids" id="assigned_client_ids" value="">
                                    <span class="help-text">Search and add one or more clients who can use this coupon</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discount Type<span class="required" aria-required="true"> *</span></label>
                                    <select name="discount_type" id="discount_type" class="form-control" required>
                                        <option value="fixed" <?= $discount_type == 'fixed' ? 'selected' : '' ?>>Fixed Amount (₹)</option>
                                        <option value="percentage" <?= $discount_type == 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                    </select>
                                    <span class="help-text">Choose between fixed amount or percentage discount</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discount Value<span class="required" aria-required="true"> *</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon discount-symbol">₹</span>
                                        <?= form_input($data_discount); ?>
                                    </div>
                                    <span class="help-block help-block-error" for="discount"><?= $validation->showError('discount') ?></span>
                                    <span class="help-text" id="discount_help">Enter discount amount (e.g., 100 for ₹100 discount)</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Minimum Order Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">₹</span>
                                        <?= form_input($data_min_order_amount); ?>
                                    </div>
                                    <span class="help-block help-block-error" for="min_order_amount"><?= $validation->showError('min_order_amount') ?></span>
                                    <span class="help-text">Minimum cart value required to apply this coupon (optional)</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Valid From<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_valid_from); ?>
                                    <span class="help-block help-block-error" for="valid_from"><?= $validation->showError('valid_from') ?></span>
                                    <span class="help-text">Date when coupon becomes active</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Valid To</label>
                                    <?= form_input($data_valid_to); ?>
                                    <span class="help-block help-block-error" for="valid_to"><?= $validation->showError('valid_to') ?></span>
                                    <span class="help-text">Leave empty if coupon never expires</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Total Usage Limit</label>
                                    <?= form_input($data_usage_limit); ?>
                                    <span class="help-block help-block-error" for="usage_limit"><?= $validation->showError('usage_limit') ?></span>
                                    <span class="help-text">Maximum times this coupon can be used (leave empty for unlimited)</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Usage Per Client<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_max_usage_per_client); ?>
                                    <span class="help-block help-block-error" for="max_usage_per_client"><?= $validation->showError('max_usage_per_client') ?></span>
                                    <span class="help-text">How many times a single client can use this coupon</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Description</label>
                                    <?= form_textarea($data_description); ?>
                                    <span class="help-block help-block-error" for="description"><?= $validation->showError('description') ?></span>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                          <div class="col-md-offset-3 col-md-9">
                            <?php echo form_submit($arr_submit)?>
                            <a href="<?php echo base_url();?>coupon"><button type="button" class="btn default">Cancel</button></a>
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

<style>
.client-picker-wrap { position: relative; }
.client-search-dropdown {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 9999;
    background: #fff; border: 1px solid #c2cad8; border-radius: 4px;
    max-height: 220px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,.15);
}
.client-search-dropdown .c-item {
    padding: 8px 12px; cursor: pointer; font-size: 13px;
    border-bottom: 1px solid #f5f5f5;
}
.client-search-dropdown .c-item:hover { background: #e8f5f3; }
.client-search-dropdown .c-item .c-name { font-weight: 600; }
.client-search-dropdown .c-item .c-sub  { font-size: 11px; color: #888; }
.selected-clients-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.client-tag {
    display: inline-flex; align-items: center; gap: 6px;
    background: #e8f5f3; color: #075e54;
    border: 1px solid #b2dfdb; border-radius: 14px;
    padding: 3px 10px; font-size: 12px; font-weight: 600;
}
.client-tag .remove-tag {
    cursor: pointer; font-size: 14px; line-height: 1;
    color: #e53935; font-weight: 700;
}
.client-tag .remove-tag:hover { color: #b71c1c; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Coupon type toggle ──────────────────────────────────────────────
    var couponTypeSelect = document.getElementById('coupon_type_select');
    var userSpecificSection = document.getElementById('user_specific_section');

    function toggleUserSpecific() {
        if (couponTypeSelect.value === 'user_specific') {
            userSpecificSection.style.display = '';
        } else {
            userSpecificSection.style.display = 'none';
        }
    }
    couponTypeSelect.addEventListener('change', toggleUserSpecific);

    // ── Client picker ───────────────────────────────────────────────────
    var selectedClients = {}; // id → {client_id, client_name, client_email}
    var searchInput   = document.getElementById('client_search_input');
    var resultsDiv    = document.getElementById('client_search_results');
    var tagsDiv       = document.getElementById('selected_clients_tags');
    var hiddenInput   = document.getElementById('assigned_client_ids');
    var searchTimer;

    function updateHidden() {
        hiddenInput.value = Object.keys(selectedClients).join(',');
    }
    function renderTags() {
        tagsDiv.innerHTML = '';
        Object.values(selectedClients).forEach(function(c) {
            var tag = document.createElement('span');
            tag.className = 'client-tag';
            tag.innerHTML = '<span>' + c.client_name + '</span><span class="remove-tag" data-id="' + c.client_id + '">&times;</span>';
            tag.querySelector('.remove-tag').addEventListener('click', function() {
                delete selectedClients[c.client_id];
                updateHidden();
                renderTags();
            });
            tagsDiv.appendChild(tag);
        });
    }
    function addClient(c) {
        selectedClients[c.client_id] = c;
        updateHidden();
        renderTags();
        searchInput.value = '';
        resultsDiv.style.display = 'none';
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        var q = this.value.trim();
        if (q.length < 2) { resultsDiv.style.display = 'none'; return; }
        searchTimer = setTimeout(function() {
            fetch('<?= base_url('coupon/clientSearch') ?>?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.clients || data.clients.length === 0) {
                        resultsDiv.innerHTML = '<div class="c-item"><span class="c-sub">No clients found</span></div>';
                    } else {
                        resultsDiv.innerHTML = data.clients.map(function(c) {
                            return '<div class="c-item" data-id="' + c.client_id + '">' +
                                '<div class="c-name">' + c.client_name + '</div>' +
                                '<div class="c-sub">' + (c.client_email || '') + (c.client_mobile_number ? ' · ' + c.client_mobile_number : '') + '</div>' +
                            '</div>';
                        }).join('');
                        resultsDiv.querySelectorAll('.c-item[data-id]').forEach(function(item) {
                            item.addEventListener('click', function() {
                                var cid = item.getAttribute('data-id');
                                var found = data.clients.filter(function(x) { return String(x.client_id) === String(cid); })[0];
                                if (found) addClient(found);
                            });
                        });
                    }
                    resultsDiv.style.display = '';
                });
        }, 280);
    });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.client-picker-wrap')) resultsDiv.style.display = 'none';
    });

    const discountTypeSelect = document.getElementById('discount_type');
    const discountSymbol = document.querySelector('.discount-symbol');
    const discountHelp = document.getElementById('discount_help');
    const discountInput = document.querySelector('.discount-input');
    
    // Function to update discount field based on type
    function updateDiscountField() {
        if (discountTypeSelect.value === 'percentage') {
            discountSymbol.textContent = '%';
            discountInput.step = '0.1';
            discountInput.max = '100';
            discountInput.placeholder = 'Enter percentage (e.g., 10 for 10%)';
            discountHelp.textContent = 'Enter discount percentage (max 100%)';
        } else {
            discountSymbol.textContent = '₹';
            discountInput.step = '0.01';
            discountInput.max = '';
            discountInput.placeholder = 'Enter discount amount';
            discountHelp.textContent = 'Enter discount amount (e.g., 100 for ₹100 discount)';
        }
    }
    
    // Initial update
    updateDiscountField();
    
    // Update on change
    discountTypeSelect.addEventListener('change', updateDiscountField);
    
    // Form validation
    const form = document.getElementById('loginform');
    form.addEventListener('submit', function(e) {
        // Clear previous error highlights
        document.querySelectorAll('.form-control').forEach(input => {
            input.classList.remove('error');
        });
        
        let isValid = true;
        
        // Validate coupon code
        const couponCode = document.getElementById('coupon_code').value.trim();
        if (!couponCode) {
            document.getElementById('coupon_code').classList.add('error');
            isValid = false;
        }
        
        // Validate discount based on type
        const discount = document.getElementById('discount').value;
        if (!discount || discount <= 0) {
            document.getElementById('discount').classList.add('error');
            isValid = false;
        }
        
        if (discountTypeSelect.value === 'percentage' && discount > 100) {
            alert('Percentage discount cannot exceed 100%');
            document.getElementById('discount').classList.add('error');
            isValid = false;
        }
        
        // Validate valid from date
        const validFrom = document.getElementById('valid_from').value;
        if (!validFrom) {
            document.getElementById('valid_from').classList.add('error');
            isValid = false;
        }
        
        // Validate usage per client
        const usagePerClient = document.getElementById('max_usage_per_client').value;
        if (!usagePerClient || usagePerClient < 1) {
            document.getElementById('max_usage_per_client').classList.add('error');
            isValid = false;
        }
        
        // Validate date range if both dates are provided
        const validTo = document.getElementById('valid_to').value;
        if (validFrom && validTo) {
            const fromDate = new Date(validFrom);
            const toDate = new Date(validTo);
            
            if (toDate < fromDate) {
                alert('Valid To date cannot be earlier than Valid From date');
                document.getElementById('valid_to').classList.add('error');
                isValid = false;
            }
        }
        
        // Validate user-specific: at least one client must be selected
        if (couponTypeSelect.value === 'user_specific' && !hiddenInput.value.trim()) {
            alert('Please select at least one client for a User Specific coupon.');
            document.getElementById('client_search_input').classList.add('error');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
            return false;
        }

        return true;
    });
    
    // Set minimum date for "valid to" based on "valid from"
    const validFromInput = document.getElementById('valid_from');
    const validToInput = document.getElementById('valid_to');
    
    validFromInput.addEventListener('change', function() {
        if (this.value) {
            validToInput.min = this.value;
            if (validToInput.value && validToInput.value < this.value) {
                validToInput.value = this.value;
            }
        }
    });
});
</script>

<?= $this->endSection();?>