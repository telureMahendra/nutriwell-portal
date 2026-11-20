<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>

<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation =  \Config\Services::validation(); ?>

<div class="content">
    <div class="page-bar">
        <?= $breadcrumb; ?>
    </div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">

                        <!-- Portlet Title: Coupon Type left, Add button right -->
                        <div class="portlet-title clearfix">
                            <div class="caption pull-left" style="font-size:16px; line-height:34px;">
                                <?= $pagetitle ?>
                            </div>
                            <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'add')) { ?>
                                <div class="pull-right">
                                    <a href="<?= base_url();?>coupon/add" class="btn green" style="margin-top:0;">
                                        Add Coupon <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="portlet-body">
                            <table class="table table-striped table-modern" id="sample_1">
                                <thead>
                                    <tr>
                                        <th width="4%">Sr. No.</th>
                                        <th width="14%">Coupon Code</th>
                                        <th width="9%">Discount</th>
                                        <th width="10%">Type</th>
                                        <th width="10%">Valid From</th>
                                        <th width="10%">Valid To</th>
                                        <th width="9%">Usage Limit</th>
                                        <th width="8%">Used</th>
                                        <th width="9%">Status</th>
                                        <th width="17%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($couponList) > 0) { 
                                        $k = 1;
                                        $current_date = date('Y-m-d');
                                        foreach ($couponList as $row) { 
                                            
                                            // Determine coupon status
                                            $status_class = '';
                                            $status_text = '';
                                            $is_expired = false;
                                            
                                            if ($row->status == 'InActive') {
                                                $status_class = 'label-danger';
                                                $status_text = 'Inactive';
                                            } elseif ($row->valid_to && $row->valid_to < $current_date) {
                                                $status_class = 'label-warning';
                                                $status_text = 'Expired';
                                                $is_expired = true;
                                            } elseif ($row->usage_limit && $row->used_count >= $row->usage_limit) {
                                                $status_class = 'label-danger';
                                                $status_text = 'Limit Reached';
                                            } else {
                                                $status_class = 'label-success';
                                                $status_text = 'Active';
                                            }
                                            
                                            // Format dates
                                            $valid_from = $row->valid_from ? date('d-m-Y', strtotime($row->valid_from)) : 'N/A';
                                            $valid_to = $row->valid_to ? date('d-m-Y', strtotime($row->valid_to)) : 'No Expiry';
                                            
                                            // Usage limit display
                                            if ($row->usage_limit === null) {
                                                $usage_limit_display = 'Unlimited';
                                            } else {
                                                $usage_limit_display = $row->usage_limit;
                                            }
                                            
                                            // Format discount based on type (if discount_type exists)
                                            $discount_display = '';
                                            if (isset($row->discount_type) && $row->discount_type == 'percentage') {
                                                $discount_display = number_format($row->discount, 1) . '%';
                                            } else {
                                                // FIXED: Changed $ to ₹ for INR
                                                $discount_display = '₹' . number_format($row->discount, 2);
                                            }
                                            ?>
                                            <tr class="odd gradeX <?= $is_expired ? 'expired-row' : '' ?>">
                                                <td><?= $k; ?></td>
                                                <td>
                                                    <strong><?= $row->coupon_code; ?></strong>
                                                    <?php if (!empty($row->description)): ?>
                                                        <br><small class="text-muted"><?= substr($row->description, 0, 30) . (strlen($row->description) > 30 ? '...' : '') ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">
                                                        <?= $discount_display ?>
                                                    </span>
                                                    <?php if (isset($row->min_order_amount) && $row->min_order_amount > 0): ?>
                                                        <br><small class="text-muted">Min. ₹<?= number_format($row->min_order_amount, 2) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (($row->coupon_type ?? 'general') === 'user_specific'): ?>
                                                        <span class="label label-info" style="background:#8e44ad;">User Specific</span>
                                                        <?php
                                                        $assignedIds = json_decode($row->assigned_client_ids ?? '[]', true) ?: [];
                                                        $count = count($assignedIds);
                                                        ?>
                                                        <br><small class="text-muted"><?= $count ?> client<?= $count !== 1 ? 's' : '' ?></small>
                                                    <?php else: ?>
                                                        <span class="label label-success" style="background:#27ae60;">General</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $valid_from; ?></td>
                                                <td>
                                                    <?= $valid_to; ?>
                                                    <?php if ($row->valid_to && $row->valid_to < $current_date): ?>
                                                        <br><small class="text-danger">Expired</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= $usage_limit_display; ?>
                                                    <?php if ($row->usage_limit !== null): ?>
                                                        <br><small>Per client: <?= $row->max_usage_per_client ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= $row->used_count; ?>
                                                    <?php if ($row->usage_limit !== null): ?>
                                                        <br><small><?= $row->usage_limit - $row->used_count ?> left</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="label <?= $status_class ?>">
                                                        <?= $status_text ?>
                                                    </span>
                                                </td>
    
                                                <td>
                                                    <?php if (CheckAccess(class_basename(service('router')->controllerName()), 'edit')) { ?>
                                                    <a href="<?= base_url(); ?>coupon/edit/<?= $row->coupon_id;?>" class="btn default btn-xs purple" title="Edit Coupon">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <?php } ?>
                                                    
                                                    <!-- Usage View Button -->
                                                    <a href="<?= base_url(); ?>coupon/usage/<?= $row->coupon_id;?>" class="btn default btn-xs blue" title="View Usage">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    
                                                    <!-- Activate/Deactivate buttons -->
                                                    <?php if ($row->status == 'Active' ) { ?>
                                                    <a href="javascript:void(0)" onclick="close_coupon(<?= $row->coupon_id; ?>)" class="btn default btn-xs red" title="Deactivate Coupon">
                                                        <i class="fa fa-times"></i>
                                                    </a>
                                                    <?php } elseif ($row->status == 'InActive' ) { ?>
                                                    <a href="javascript:void(0)" onclick="activate_coupon(<?= $row->coupon_id; ?>)" class="btn default btn-xs green" title="Activate Coupon">
                                                        <i class="fa fa-check"></i>
                                                    </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                    <?php
                                            $k++;
                                        }
                                    } else { ?>
                                        <tr>
                                            <td colspan="10" class="text-center">
                                                <div class="alert alert-info">
                                                    No coupons found. <a href="<?= base_url();?>coupon/add">Click here</a> to add your first coupon.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 

<!-- Close Coupon Modal -->
<div class="modal fade" id="close_coupon" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Deactivate Coupon</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to deactivate this coupon?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes_close">Yes</button>
                <button type="button" class="btn default" id="no_close" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<!-- Activate Coupon Modal -->
<div class="modal fade" id="activate_coupon" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Activate Coupon</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to activate this coupon?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" id="yes_activate">Yes</button>
                <button type="button" class="btn default" id="no_activate" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Deactivate coupon
function close_coupon(coupon_id) {
    $('#close_coupon').modal();
    $('#close_coupon #yes_close').off('click').on('click', function () {
        var url = '<?= base_url('coupon/close') ?>';
        $.post(url, { coupon_id: coupon_id }, function (responseText) {
            if (responseText == 1) {
                location.href = '<?= base_url('coupon') ?>';
            } else {
                alert('Something went wrong with your request');
            }
        });
    });
}

// Activate coupon
function activate_coupon(coupon_id) {
    $('#activate_coupon').modal();
    $('#activate_coupon #yes_activate').off('click').on('click', function () {
        console.log("Inside ")
        var url = '<?= base_url('coupon/activate') ?>';
        $.post(url, { coupon_id: coupon_id }, function (response) {
            // Handle both JSON and plain text responses
            try {
                var result = JSON.parse(response);
                if (result.success) {
                    location.href = '<?= base_url('coupon') ?>';
                } else {
                    alert(result.message || 'Something went wrong with your request');
                }
            } catch (e) {
                // If response is not JSON (e.g., returns "1" for success)
                if (response == "1" || response.trim() == "1") {
                    location.href = '<?= base_url('coupon') ?>';
                } else {
                    alert('Something went wrong with your request');
                }
            }
        });
    });
}

// Add DataTable functionality if not already added
$(document).ready(function() {
    // Initialize DataTable if the library is available
    if ($.fn.DataTable) {
        $('#sample_1').DataTable({
            "pageLength": 25,
            "order": [[0, "asc"]],
            "language": {
                "emptyTable": "No coupons available",
                "info": "Showing _START_ to _END_ of _TOTAL_ coupons",
                "infoEmpty": "Showing 0 to 0 of 0 coupons",
                "infoFiltered": "(filtered from _MAX_ total coupons)",
                "lengthMenu": "Show _MENU_ coupons",
                "search": "Search coupons:",
                "zeroRecords": "No matching coupons found"
            }
        });
    }
    
    // Style expired rows
    $('.expired-row').css('opacity', '0.7');
});
</script>

<style>
.label {
    padding: 3px 8px;
    font-size: 11px;
    border-radius: 3px;
}
.label-success {
    background-color: #32c5d2;
}
.label-danger {
    background-color: #e7505a;
}
.label-warning {
    background-color: #c49f47;
}
.badge-primary {
    background-color: #337ab7;
    padding: 4px 8px;
    border-radius: 3px;
    color: white;
    font-weight: bold;
}
.text-muted {
    color: #888;
}
.expired-row {
    background-color: #fff8e1;
}
</style>

<?= $this->endSection(); ?>