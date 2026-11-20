<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<style>
.form-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.06); padding:28px; }
.reward-box { background:#fffbf0; border:1px solid #ffe0a0; border-radius:8px; padding:18px; margin-top:10px; }
.btn-save { background:#f57f27 !important; color:#fff !important; padding:10px 30px; font-weight:600; }
.section-title { font-size:14px; font-weight:700; color:#555; margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid #eee; }
</style>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="portlet light bordered">
            <div class="portlet-body">
                <form action="<?= base_url('mentor/challenges/store') ?>" method="POST" enctype="multipart/form-data">

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?php $err = session()->getFlashdata('error');
                            echo is_array($err) ? implode('<br>', $err) : $err; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-card">
                        <div class="section-title"><i class="fa fa-trophy text-warning"></i> Create New Challenge</div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Challenge Title <span class="required">*</span></label>
                                    <input type="text" name="title" class="form-control"
                                           placeholder="e.g. 4 Days Yoga Challenge"
                                           value="<?= old('title') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive (Draft)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Describe what the user needs to do each day..."><?= old('description') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Activity Type <span class="required">*</span></label>
                                    <select name="activity_type" class="form-control" id="activityType" required>
                                        <option value="">— Select —</option>
                                        <option value="water"      <?= old('activity_type') == 'water'      ? 'selected' : '' ?>>💧 Water</option>
                                        <option value="steps"      <?= old('activity_type') == 'steps'      ? 'selected' : '' ?>>🚶 Steps</option>
                                        <option value="meditation" <?= old('activity_type') == 'meditation' ? 'selected' : '' ?>>🧘 Meditation</option>
                                        <option value="sleep"      <?= old('activity_type') == 'sleep'      ? 'selected' : '' ?>>😴 Sleep</option>
                                        <option value="workout"    <?= old('activity_type') == 'workout'    ? 'selected' : '' ?>>💪 Workout</option>
                                        <option value="yoga"       <?= old('activity_type') == 'yoga'       ? 'selected' : '' ?>>🪷 Yoga</option>
                                        <option value="custom"     <?= old('activity_type') == 'custom'     ? 'selected' : '' ?>>⭐ Custom</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Daily Target Value <span class="required">*</span></label>
                                    <input type="number" name="target_value" class="form-control"
                                           placeholder="e.g. 30" min="1" step="0.1"
                                           value="<?= old('target_value') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Unit</label>
                                    <input type="text" name="target_unit" class="form-control"
                                           placeholder="e.g. minutes" id="targetUnit"
                                           value="<?= old('target_unit') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Streak Days Required <span class="required">*</span></label>
                                    <input type="number" name="target_days" class="form-control"
                                           placeholder="e.g. 4" min="1"
                                           value="<?= old('target_days') ?>" required>
                                    <span class="help-block">User must check-in for this many consecutive days.</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Start Date <small class="text-muted">(optional)</small></label>
                                    <input type="date" name="start_date" class="form-control" value="<?= old('start_date') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>End Date <small class="text-muted">(optional)</small></label>
                                    <input type="date" name="end_date" class="form-control" value="<?= old('end_date') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Challenge Banner Image</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                        </div>

                        <div class="reward-box">
                            <div class="section-title"><i class="fa fa-gift text-danger"></i> Completion Reward</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Reward Points <span class="required">*</span></label>
                                        <input type="number" name="points" class="form-control"
                                               placeholder="e.g. 50" min="0"
                                               value="<?= old('points', 50) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Reward Type <span class="required">*</span></label>
                                        <select name="reward_type" class="form-control" required>
                                            <option value="points">Points Only</option>
                                            <option value="appointment_unlock">Free Appointment Token</option>
                                            <option value="both">Points + Free Appointment</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions" style="margin-top:24px;text-align:right;">
                            <a href="<?= base_url('mentor/challenges') ?>" class="btn btn-default">Cancel</a>
                            &nbsp;
                            <button type="submit" class="btn btn-save">
                                <i class="fa fa-check"></i> Create Challenge
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const unitMap = { water:'glasses', steps:'steps', meditation:'minutes', sleep:'hours', workout:'minutes', yoga:'minutes', custom:'' };
document.getElementById('activityType').addEventListener('change', function() {
    var u = document.getElementById('targetUnit');
    if (!u.value) u.value = unitMap[this.value] || '';
});
</script>

<?= $this->endSection(); ?>
