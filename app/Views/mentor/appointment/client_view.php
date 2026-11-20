<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

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
                            <button type="button" class="btn btn-sm green"
                                    onclick="window.openChatWith && window.openChatWith(<?= (int) $clientDetails->client_id ?>)">
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
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="40%">Name:</th>
                                                <td><?= esc($clientDetails->client_name); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Phone:</th>
                                                <td><?= esc($clientDetails->client_mobile_number); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td><?= esc($clientDetails->client_email); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Age:</th>
                                                <td><?= esc($clientDetails->age); ?> years</td>
                                            </tr>
                                            <tr>
                                                <th>Gender:</th>
                                                <td><?= esc($clientDetails->client_gender); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Height:</th>
                                                <td><?= esc($clientDetails->height); ?> <?= esc($clientDetails->height_in); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Weight:</th>
                                                <td><?= esc($clientDetails->weight); ?> kg</td>
                                            </tr>
                                            <?php if ($clientDetails->lifestyle_name): ?>
                                            <tr>
                                                <th>Lifestyle:</th>
                                                <td><?= esc($clientDetails->lifestyle_name); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if ($clientDetails->goal_name): ?>
                                            <tr>
                                                <th>Goal:</th>
                                                <td><?= esc($clientDetails->goal_name); ?></td>
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
                                            <i class="fa fa-calendar"></i> Appointment History
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <?php if (!empty($appointmentHistory)): ?>
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Slot</th>
                                                        <th>Plan</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($appointmentHistory as $appointment): ?>
                                                    <tr>
                                                        <td><?= date('d/m/Y', strtotime($appointment->date)); ?></td>
                                                        <td><?= esc($appointment->slot); ?></td>
                                                        <td><?= esc($appointment->plan_name); ?></td>
                                                        <td>
                                                            <span class="label label-<?= ($appointment->status == 'Completed') ? 'success' : 'warning'; ?>">
                                                                <?= $appointment->status; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="<?= base_url('mentor/appointment/view/' . $appointment->appointment_id); ?>" class="btn btn-xs blue">
                                                                <i class="fa fa-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <div class="alert alert-info">No appointment history found.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet box yellow">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-comments"></i> Client Comments
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <?php if (!empty($clientComments)): ?>
                                            <div class="timeline">
                                                <?php foreach ($clientComments as $comment): ?>
                                                <div class="timeline-item">
                                                    <div class="timeline-badge">
                                                        <i class="fa fa-comment"></i>
                                                    </div>
                                                    <div class="timeline-body">
                                                        <div class="timeline-body-arrow"></div>
                                                        <div class="timeline-body-head">
                                                            <div class="timeline-body-head-caption">
                                                                <span class="timeline-body-alerttitle"><?= esc($comment->doctor_name); ?></span>
                                                                <span class="timeline-body-time"><?= date('d/m/Y H:i', strtotime($comment->date)); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="timeline-body-content">
                                                            <?= nl2br(esc($comment->comment)); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">No comments found.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Set Targets -->
            <div class="row" style="margin-top:16px;">
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
                            <form method="post" action="<?= base_url('mentor/clients/set_targets/' . $clientDetails->client_id); ?>">
                                <?= csrf_field(); ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Water Target <small>(glasses/day)</small></label>
                                            <input type="number" class="form-control" name="target_water"
                                                   value="<?= $target_water; ?>" min="1" max="30" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Sleep Target <small>(minutes/day)</small></label>
                                            <input type="number" class="form-control" name="target_sleep"
                                                   value="<?= $target_sleep; ?>" min="60" max="900" required>
                                            <small class="text-muted">e.g. 480 = 8 hrs</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Calorie Intake Target <small>(kcal/day)</small></label>
                                            <input type="number" class="form-control" name="target_calorie"
                                                   value="<?= $target_calorie; ?>" min="500" max="6000" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Steps Target <small>(steps/day)</small></label>
                                            <input type="number" class="form-control" name="target_steps"
                                                   value="<?= $target_steps; ?>" min="500" max="50000" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Weight Target <small>(kg)</small></label>
                                            <input type="number" step="0.1" class="form-control" name="target_weight"
                                                   value="<?= $target_weight; ?>" min="20" max="300" required>
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
</div>

<?= $this->endSection(); ?>