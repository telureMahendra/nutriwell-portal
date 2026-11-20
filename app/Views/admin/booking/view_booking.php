<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php $validation = \Config\Services::validation(); ?>
<div class="content">
    <style type="text/css">
        span.help-block {
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
                        
                        
                    </div>
                </div>
                <div class="portlet-body form">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Client Name<span class="required" aria-required="true">
                                            *</span></label>
                                    <span class="form-control form-control-view"><?= $bookingArray[0]->client_name; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Language<span class="required" aria-required="true">
                                            *</span></label>
                                    <span class="form-control form-control-view"><?= $bookingArray[0]->language_name; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">City Name<span class="required" aria-required="true"> *</span></label>
                                    <span class="form-control form-control-view"><?= $bookingArray[0]->city_name; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Plan Name<span class="required" aria-required="true">
                                            *</span></label>
                                    <span class="form-control form-control-view"><?= $bookingArray[0]->name; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Plan Duration<span class="required" aria-required="true">
                                            *</span></label>
                                    <span class="form-control form-control-view"><?= $bookingArray[0]->plan_duration; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Sub Total<span class="required" aria-required="true">
                                            *</span></label>
                                    <span class="form-control form-control-view"><?= $bookingArray[0]->subtotal; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Coupon Code<span class="required" aria-required="true">
                                            *</span></label>
                                    <span class="form-control form-control-view"><?= $bookingArray[0]->coupon_code; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discount Amount<span class="required" aria-required="true">
                                            *</span></label>
                                    <span class="form-control form-control-view"><?= $bookingArray[0]->discount; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Total Amount<span class="required" aria-required="true">*</span></label>
                                    <span class="form-control form-control-view"><?= $bookingArray[0]->total_amount; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <a href="<?php echo base_url(); ?>booking"><button type="button" class="btn default">Cancel</button></a>
                            </div>
                            <div class="col-md-6"> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>