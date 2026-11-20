<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.css">

<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<?php 
    $validation = \Config\Services::validation(); 
    $client = $clientDetails[0];
?>

<div class="content">
    <div class="page-bar"> <?= $breadcrumb; ?> </div>
    <div class="page-container">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet box blue">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-user"></i> <?= $pagetitle ?>
                        </div>
                        <div class="actions">
                            <?php if (empty($activePlanDetails)): ?>
                                <a href="<?= base_url('clients/add_subscription/' . $client->client_id); ?>" class="btn btn-sm purple-plum">
                                    <i class="fa fa-plus-circle"></i> Add Subscription
                                </a>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm green"
                                    onclick="window.openChatWith && window.openChatWith(<?= (int) $client->client_id ?>)">
                                <i class="fa fa-comments"></i> Chat with Client
                            </button>
                        </div>
                        <div class="tools">
                            <a href="javascript:;" class="collapse"></a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="portlet box green">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-info-circle"></i> Client Information
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="text-center" style="margin-bottom: 20px;">
                                            <?php if(!empty($client->client_profile_pic) && file_exists('./public/uploads/client/'.$client->client_profile_pic)): ?>
                                                <img src="<?= base_url().'public/uploads/client/' . $client->client_profile_pic; ?>" alt="Client Image" class="img-circle" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #eee;">
                                            <?php else: ?>
                                                <img src="<?= base_url().'public/assets/admin/layout/img/avatar.png'; ?>" alt="Client Image" class="img-circle" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #eee;">
                                            <?php endif; ?>
                                        </div>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="40%">Name:</th>
                                                <td><?= esc($client->client_name); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Phone:</th>
                                                <td><?= esc($client->client_mobile_number); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td><?= esc($client->client_email); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Age:</th>
                                                <td><?= esc($client->age); ?> years</td>
                                            </tr>
                                            <tr>
                                                <th>Gender:</th>
                                                <td><?= esc($client->client_gender); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Height:</th>
                                                <td><?= esc($client->height); ?> <?= esc($client->height_in); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Weight:</th>
                                                <td><?= esc($client->weight); ?> kg</td>
                                            </tr>
                                            <?php if ($client->lifestyle_id): ?>
                                            <tr>
                                                <th>Lifestyle:</th>
                                                <td><?= esc($client->lifestyle_id); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if ($client->goal_id): ?>
                                            <tr>
                                                <th>Goal:</th>
                                                <td><?= esc($client->goal_id); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="portlet box blue">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-star"></i> Active Plan Details
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <?php if (!empty($activePlanDetails)): ?>
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Plan Name</th>
                                                        <th>Start Date</th>
                                                        <th>Expiry Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($activePlanDetails as $plan): ?>
                                                    <tr>
                                                        <td><?= esc($plan->name ?? $plan->plan_name ?? 'N/A'); ?></td>
                                                        <td><?= date('d/m/Y', strtotime($plan->plan_start_date ?? $plan->created_on ?? date('Y-m-d'))); ?></td>
                                                        <td><?= date('d/m/Y', strtotime($plan->plan_expiry_date ?? date('Y-m-d'))); ?></td>
                                                        <td>
                                                            <span class="label label-success">Active</span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <div class="alert alert-warning">No active subscription plan found.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="portlet box yellow">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-history"></i> Plans History
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <?php if (!empty($allPlansHistory)): ?>
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Booking ID</th>
                                                        <th>Plan Name</th>
                                                        <th>Amount</th>
                                                        <th>Purchase Date</th>
                                                        <th>Expiry Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($allPlansHistory as $history): ?>
                                                    <tr>
                                                        <td>#<?= esc($history->booking_id ?? 'N/A'); ?></td>
                                                        <td><?= esc($history->plan_name ?? 'N/A'); ?></td>
                                                        <td><?= esc($history->amount_paid ?? 0); ?></td>
                                                        <td><?= esc($history->purchased_date ?? 'N/A'); ?></td>
                                                        <td><?= esc($history->expiry_date ?? 'N/A'); ?></td>
                                                        <td>
                                                            <span class="label label-<?= (!empty($history->current_status) && ($history->current_status == 'Active' || $history->current_status == 'Currently Active')) ? 'success' : 'default'; ?>">
                                                                <?= esc($history->current_status ?? 'Unknown'); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <div class="alert alert-info">No plan history available.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Set Targets -->
            <div class="col-md-12">
                <div class="portlet box red-intense">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-bullseye"></i> Set Daily Targets
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?php if (session()->getFlashdata('suc_msg')): ?>
                            <div class="alert alert-success"><?= session()->getFlashdata('suc_msg'); ?></div>
                        <?php endif; ?>
                        <form method="post" action="<?= base_url('clients/set_targets/' . $client->client_id); ?>">
                            <?= csrf_field(); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Water Target <small>(glasses/day)</small></label>
                                        <input type="number" class="form-control" name="target_water"
                                               value="<?= $target_water ?? 8; ?>" min="1" max="30" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Sleep Target <small>(minutes/day)</small></label>
                                        <input type="number" class="form-control" name="target_sleep"
                                               value="<?= $target_sleep ?? 480; ?>" min="60" max="900" required>
                                        <small class="text-muted">e.g. 480 = 8 hrs</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Calorie Intake Target <small>(kcal/day)</small></label>
                                        <input type="number" class="form-control" name="target_calorie"
                                               value="<?= $target_calorie ?? 2000; ?>" min="500" max="6000" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Steps Target <small>(steps/day)</small></label>
                                        <input type="number" class="form-control" name="target_steps"
                                               value="<?= $target_steps ?? 10000; ?>" min="500" max="50000" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Weight Target <small>(kg)</small></label>
                                        <input type="number" step="0.1" class="form-control" name="target_weight"
                                               value="<?= $target_weight ?? 0; ?>" min="0" max="300" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" style="padding-top:24px;">
                                        <button type="submit" class="btn green btn-block">
                                            <i class="fa fa-save"></i> Save Targets
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include All Modals (keeping these in case they are needed globally) -->
<?= $this->include('admin/clients/partials/modals'); ?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0"></script>
<?= $this->endSection(); ?>
