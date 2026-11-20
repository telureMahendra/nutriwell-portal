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
    <div class="page-bar"> <?= $breadcrumb; ?> </div>
    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="portlet box grey-cascade">
                <div class="portlet-title">
                    <div class="caption">
                        
                        <?= $pagetitle ?>
                    </div>
                </div>
                <div class="portlet-body form">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Plan Category </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->category; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Plan Name </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->planname; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Date </label>
                                    <span class="form-control form-control-view">
                                    <td><?php echo date('d/m/Y',strtotime($appointmentDetails[0]->date));?></td></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Slot </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->slot; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Client Name </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->client_name; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Phone No </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->client_mobile_number; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Weight </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->weight; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Height </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->height .' / '. $appointmentDetails[0]->height_in;?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Lifestyle </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->lifestyle; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Goal </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->goal;?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Age </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->age; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"> Diet </label>
                                    <span class="form-control form-control-view"><?= $appointmentDetails[0]->diet;?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions right">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <a href="<?php echo base_url(); ?>mentor/appointment"><button type="button" class="btn default">Cancel</button></a>
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