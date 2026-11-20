<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<style>
.form-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.06); padding:28px; }
.reward-box { background:#fffbf0; border:1px solid #ffe0a0; border-radius:8px; padding:18px; margin-top:10px; }
.btn-save { background:#f57f27 !important; color:#fff !important; padding:10px 30px; font-weight:600; }
.section-title { font-size:14px; font-weight:700; color:#555; margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid #eee; }
.img-preview { width:120px; height:80px; object-fit:cover; border-radius:6px; border:1px solid #ddd; }
</style>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="portlet light bordered">
            <div class="portlet-body">
                <form action="<?= base_url('mentor/challenges/update/' . $challenge['id']) ?>" method="POST" enctype="multipart/form-data">

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?php $err = session()->getFlashdata('error');
                            echo is_array($err) ? implode('<br>', $err) : $err; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-card">
                        <div class="section-title"><i class="fa fa-pencil text-warning"></i> Edit Challenge</div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Challenge Title <span class="required">*</span></label>
                                    <input type="text" name="title" class="form-control"
                                           value="<?= old('title', esc($challenge['title'])) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="Active"   <?= $challenge['status'] == 'Active'   ? 'selected' : '' ?>>Active</option>
                                        <option value="Inactive" <?= $challenge['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= old('description', esc($challenge['description'])) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Activity Type <span class="required">*</span></label>
                                    <select name="activity_type" class="form-control" required>
                                        <?php
                                        $types = ['water'=>'💧 Water','steps'=>'🚶 Steps','meditation'=>'🧘 Meditation','sleep'=>'😴 Sleep','workout'=>'💪 Workout','yoga'=>'🪷 Yoga','custom'=>'⭐ Custom'];
                                        foreach ($types as $val => $label):
                                            $sel = (old('activity_type', $challenge['activity_type']) == $val) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Daily Target Value <span class="required">*</span></label>
                                    <input type="number" name="target_value" class="form-control" step="0.1" min="1"
                                           value="<?= old('target_value', $challenge['target_value']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Unit</label>
                                    <input type="text" name="target_unit" class="form-control"
                                           value="<?= old('target_unit', esc($challenge['target_unit'] ?? '')) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Streak Days Required <span class="required">*</span></label>
                                    <input type="number" name="target_days" class="form-control" min="1"
                                           value="<?= old('target_days', $challenge['target_days']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control"
                                           value="<?= old('start_date', $challenge['start_date']) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control"
                                           value="<?= old('end_date', $challenge['end_date']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Challenge Banner Image</label>
                            <?php if (!empty($challenge['image_url'])): ?>
                                <div style="margin-bottom:8px;">
                                    <img src="<?= base_url($challenge['image_url']) ?>" class="img-preview" alt="">
                                    <small class="text-muted" style="display:block;">Current image — upload a new one to replace it.</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                        </div>

                        <div class="reward-box">
                            <div class="section-title"><i class="fa fa-gift text-danger"></i> Completion Reward</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Reward Points <span class="required">*</span></label>
                                        <input type="number" name="points" class="form-control" min="0"
                                               value="<?= old('points', $challenge['points']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Reward Type <span class="required">*</span></label>
                                        <select name="reward_type" class="form-control" required>
                                            <?php
                                            $rtypes = ['points'=>'Points Only','appointment_unlock'=>'Free Appointment Token','both'=>'Points + Free Appointment'];
                                            foreach ($rtypes as $rv => $rl):
                                                $sel = (old('reward_type', $challenge['reward_type']) == $rv) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $rv ?>" <?= $sel ?>><?= $rl ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions" style="margin-top:24px;text-align:right;">
                            <a href="<?= base_url('mentor/challenges') ?>" class="btn btn-default">Cancel</a>
                            &nbsp;
                            <button type="submit" class="btn btn-save">
                                <i class="fa fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
