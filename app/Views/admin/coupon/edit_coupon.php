<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>
<div class="content">
    <style type="text/css">
        span.help-block{ color: #f30; }
        .help-text { font-size: 12px; color: #888; margin-top: 5px; display: block; }
        .form-control.error { border-color: #f30; }
        .client-picker-wrap { position: relative; }
        .client-search-dropdown {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 9999;
            background: #fff; border: 1px solid #c2cad8; border-radius: 4px;
            max-height: 220px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,.15);
        }
        .client-search-dropdown .c-item { padding: 8px 12px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f5f5f5; }
        .client-search-dropdown .c-item:hover { background: #e8f5f3; }
        .client-search-dropdown .c-item .c-name { font-weight: 600; }
        .client-search-dropdown .c-item .c-sub  { font-size: 11px; color: #888; }
        .selected-clients-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .client-tag { display: inline-flex; align-items: center; gap: 6px; background: #e8f5f3; color: #075e54; border: 1px solid #b2dfdb; border-radius: 14px; padding: 3px 10px; font-size: 12px; font-weight: 600; }
        .client-tag .remove-tag { cursor: pointer; font-size: 14px; line-height: 1; color: #e53935; font-weight: 700; }
        .client-tag .remove-tag:hover { color: #b71c1c; }
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
                    echo form_open_multipart('coupon/edit/'.$couponDetails->coupon_id, $attributes);

                    // Form values from database
                    $coupon_code = set_value('coupon_code', $couponDetails->coupon_code);                
                    $data_coupon_code = array(
                        'name'          => 'coupon_code',
                        'id'            => 'coupon_code',
                        'value'         => $coupon_code,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'placeholder'   => 'Enter coupon code (e.g., WELCOME10)'
                    );
                    
                    $discount = set_value('discount', $couponDetails->discount);                  
                    $data_discount = array(
                        'name'          => 'discount',
                        'id'            => 'discount',
                        'type'          => 'number',
                        'value'         => $discount,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'step'          => '0.01',
                        'min'           => '0',
                        'placeholder'   => 'Enter discount amount'
                    );
                    
                    // Validity Dates
                    $valid_from = set_value('valid_from', $couponDetails->valid_from ? date('Y-m-d', strtotime($couponDetails->valid_from)) : date('Y-m-d'));                  
                    $data_valid_from = array(
                        'name'          => 'valid_from',
                        'id'            => 'valid_from',
                        'type'          => 'date',
                        'value'         => $valid_from,
                        'class'         => 'form-control',
                        'required'      => 'required',
                        'min'           => date('Y-m-d')
                    );
                    
                    $valid_to = set_value('valid_to', $couponDetails->valid_to ? date('Y-m-d', strtotime($couponDetails->valid_to)) : '');                  
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
                    $usage_limit = set_value('usage_limit', $couponDetails->usage_limit);                  
                    $data_usage_limit = array(
                        'name'          => 'usage_limit',
                        'id'            => 'usage_limit',
                        'type'          => 'number',
                        'value'         => $usage_limit,
                        'class'         => 'form-control',
                        'min'           => '1',
                        'placeholder'   => 'Leave empty for unlimited usage'
                    );
                    
                    $max_usage_per_client = set_value('max_usage_per_client', $couponDetails->max_usage_per_client ?: 1);                  
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
                    $description = set_value('description', $couponDetails->description);                  
                    $data_description = array(
                        'name'          => 'description',
                        'id'            => 'description',
                        'value'         => $description,
                        'class'         => 'form-control',
                        'rows'          => '3',
                        'placeholder'   => 'Optional description (visible to admin only)'
                    );
                    
                    // Status (read-only display)
                    $status_display = $couponDetails->status == 'Active' ? 
                        '<span class="label label-success">Active</span>' : 
                        '<span class="label label-danger">Inactive</span>';
                    
                    $arr_submit = array(
                        'name'          => 'submit',
                        'value'         => $pagetitle,
                        'class'         => 'btn green'
                    );
                    ?>
                    
                    <!-- Display usage statistics -->
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h4>Coupon Usage Statistics</h4>
                                <p>
                                    <strong>Times Used:</strong> <?= $couponDetails->used_count ?> 
                                    <?php if ($couponDetails->usage_limit !== null): ?>
                                        (<?= $couponDetails->usage_limit - $couponDetails->used_count ?> remaining)
                                    <?php endif; ?>
                                    | 
                                    <strong>Status:</strong> <?= $status_display ?>
                                    <?php if ($couponDetails->valid_to && strtotime($couponDetails->valid_to) < time()): ?>
                                        | <span class="text-danger"><strong>Note:</strong> This coupon has expired</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <?php
                    $existingType      = $couponDetails->coupon_type ?? 'general';
                    $existingClientIds = $couponDetails->assigned_client_ids ?? '';
                    // Build JS-safe pre-populated clients array
                    $preClients = [];
                    foreach ($assignedClients as $ac) {
                        $preClients[] = [
                            'client_id'   => $ac->client_id,
                            'client_name' => $ac->client_name,
                            'client_email'=> $ac->client_email ?? '',
                        ];
                    }
                    ?>
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Coupon Code<span class="required" aria-required="true"> *</span></label>
                                    <?= form_input($data_coupon_code); ?>
                                    <span class="help-block help-block-error" for="coupon_code"><?= $validation->showError('coupon_code') ?></span>
                                    <span class="help-text">Unique code that clients will enter</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Coupon Type<span class="required" aria-required="true"> *</span></label>
                                    <select name="coupon_type" id="coupon_type_select" class="form-control" required>
                                        <option value="general" <?= $existingType === 'general' ? 'selected' : '' ?>>General (available to all clients)</option>
                                        <option value="user_specific" <?= $existingType === 'user_specific' ? 'selected' : '' ?>>User Specific (assigned clients only)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- User-specific client picker -->
                        <div class="row" id="user_specific_section" style="display:<?= $existingType === 'user_specific' ? '' : 'none' ?>;">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Assigned Clients</label>
                                    <div class="client-picker-wrap">
                                        <input type="text" id="client_search_input" class="form-control" placeholder="Search client by name, email or phone…" autocomplete="off">
                                        <div id="client_search_results" class="client-search-dropdown" style="display:none;"></div>
                                    </div>
                                    <div id="selected_clients_tags" class="selected-clients-tags"></div>
                                    <input type="hidden" name="assigned_client_ids" id="assigned_client_ids" value="<?= esc(implode(',', array_column($preClients, 'client_id'))) ?>">
                                    <span class="help-text">Add or remove clients who can use this coupon</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discount Type<span class="required" aria-required="true"> *</span></label>
                                    <?php $savedDiscountType = set_value('discount_type', $couponDetails->discount_type ?? 'fixed'); ?>
                                    <select name="discount_type" id="discount_type" class="form-control" required>
                                        <option value="fixed"      <?= $savedDiscountType === 'fixed'      ? 'selected' : '' ?>>Fixed Amount (₹)</option>
                                        <option value="percentage" <?= $savedDiscountType === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discount Value<span class="required" aria-required="true"> *</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon discount-symbol"><?= ($savedDiscountType === 'percentage') ? '%' : '₹' ?></span>
                                        <?= form_input($data_discount); ?>
                                    </div>
                                    <span class="help-block help-block-error" for="discount"><?= $validation->showError('discount') ?></span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Coupon type toggle ──────────────────────────────────────────────
    var couponTypeSelect    = document.getElementById('coupon_type_select');
    var userSpecificSection = document.getElementById('user_specific_section');
    couponTypeSelect.addEventListener('change', function() {
        userSpecificSection.style.display = this.value === 'user_specific' ? '' : 'none';
    });

    // ── Client picker (pre-populate from server) ────────────────────────
    var preClients = <?= json_encode($preClients) ?>;
    var selectedClients = {};
    preClients.forEach(function(c) { selectedClients[c.client_id] = c; });

    var searchInput = document.getElementById('client_search_input');
    var resultsDiv  = document.getElementById('client_search_results');
    var tagsDiv     = document.getElementById('selected_clients_tags');
    var hiddenInput = document.getElementById('assigned_client_ids');
    var searchTimer;

    function updateHidden() { hiddenInput.value = Object.keys(selectedClients).join(','); }
    function renderTags() {
        tagsDiv.innerHTML = '';
        Object.values(selectedClients).forEach(function(c) {
            var tag = document.createElement('span');
            tag.className = 'client-tag';
            tag.innerHTML = '<span>' + c.client_name + '</span><span class="remove-tag" data-id="' + c.client_id + '">&times;</span>';
            tag.querySelector('.remove-tag').addEventListener('click', function() {
                delete selectedClients[c.client_id]; updateHidden(); renderTags();
            });
            tagsDiv.appendChild(tag);
        });
    }
    function addClient(c) {
        selectedClients[c.client_id] = c; updateHidden(); renderTags();
        searchInput.value = ''; resultsDiv.style.display = 'none';
    }
    renderTags(); // show pre-populated tags immediately

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

    // ── Discount type symbol sync ───────────────────────────────────────
    var discountTypeEl = document.getElementById('discount_type');
    var symbolEl       = document.querySelector('.discount-symbol');
    if (discountTypeEl && symbolEl) {
        discountTypeEl.addEventListener('change', function() {
            symbolEl.textContent = this.value === 'percentage' ? '%' : '₹';
        });
    }

    // ── Form validation ─────────────────────────────────────────────────
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
        
        // Validate discount
        const discount = document.getElementById('discount').value;
        if (!discount || discount <= 0) {
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
        
        // Validate usage limit not below current used count
        const usageLimit = document.getElementById('usage_limit').value;
        const currentUsed = <?= $couponDetails->used_count ?>;
        if (usageLimit && parseInt(usageLimit) < currentUsed) {
            alert('Usage limit cannot be less than current usage count (' + currentUsed + ')');
            document.getElementById('usage_limit').classList.add('error');
            isValid = false;
        }

        // Validate user-specific: at least one client required
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
    
    // Warn if trying to set usage limit below current usage
    const usageLimitInput = document.getElementById('usage_limit');
    usageLimitInput.addEventListener('change', function() {
        const currentUsed = <?= $couponDetails->used_count ?>;
        if (this.value && parseInt(this.value) < currentUsed) {
            if (!confirm('Warning: Setting usage limit below current usage (' + currentUsed + ') will prevent further usage. Continue?')) {
                this.value = '';
            }
        }
    });
});
</script>

<?= $this->endSection();?>