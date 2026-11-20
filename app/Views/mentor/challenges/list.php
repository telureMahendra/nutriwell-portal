<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<style>
.challenge-card { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:20px; transition:transform .2s; }
.challenge-card:hover { transform:translateY(-4px); }
.challenge-img { height:140px; object-fit:cover; border-radius:10px 10px 0 0; width:100%; }
.challenge-img-placeholder { height:140px; background:#f5f5f5; border-radius:10px 10px 0 0; display:flex; align-items:center; justify-content:center; color:#ccc; }
.challenge-body { padding:14px; }
.challenge-title { font-weight:700; font-size:15px; color:#333; margin-bottom:4px; }
.badge-type { padding:2px 8px; border-radius:12px; font-size:11px; font-weight:600; text-transform:uppercase; }
.badge-water      { background:#e3f2fd; color:#1565c0; }
.badge-steps      { background:#e8f5e9; color:#2e7d32; }
.badge-meditation { background:#f3e5f5; color:#6a1b9a; }
.badge-sleep      { background:#fff3e0; color:#e65100; }
.badge-workout    { background:#fce4ec; color:#b71c1c; }
.badge-yoga       { background:#e0f2f1; color:#00695c; }
.badge-custom     { background:#eeeeee; color:#424242; }
.own-badge { font-size:10px; background:#fff3e0; color:#e65100; border-radius:4px; padding:1px 5px; }
.btn-orange { background:#f57f27 !important; color:#fff !important; border:none; }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption font-dark">
                    <i class="fa fa-trophy"></i>
                    <span class="caption-subject bold uppercase"> Challenges</span>
                </div>
                <div class="actions">
                    <a href="<?= base_url('mentor/challenges/create') ?>" class="btn btn-sm btn-orange btn-circle">
                        <i class="fa fa-plus"></i> Create Challenge
                    </a>
                </div>
            </div>

            <div class="portlet-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <?= is_array(session()->getFlashdata('error'))
                            ? implode('<br>', session()->getFlashdata('error'))
                            : session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($challenges)): ?>
                <div class="row">
                    <?php foreach ($challenges as $c): ?>
                    <?php $isOwn = ($c['created_by'] == $mentor_id && $c['creator_type'] === 'Mentor'); ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="challenge-card" style="<?= $isOwn ? 'border-left:3px solid #f57f27;' : '' ?>">
                            <?php if (!empty($c['image_url'])): ?>
                                <img src="<?= base_url($c['image_url']) ?>" class="challenge-img" alt="">
                            <?php else: ?>
                                <div class="challenge-img-placeholder"><i class="fa fa-trophy fa-3x"></i></div>
                            <?php endif; ?>

                            <div class="challenge-body">
                                <div class="challenge-title">
                                    <?= esc($c['title']) ?>
                                    <?php if ($isOwn): ?>
                                        <span class="own-badge">My Challenge</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size:12px;color:#888;margin-bottom:8px;">
                                    <span class="badge-type badge-<?= strtolower($c['activity_type']) ?>">
                                        <?= ucfirst($c['activity_type']) ?>
                                    </span>
                                    &nbsp;·&nbsp;<?= $c['target_days'] ?> day streak &nbsp;·&nbsp; <?= $c['points'] ?> pts
                                </div>
                                <p class="text-muted" style="font-size:12px;min-height:30px;">
                                    <?= character_limiter(strip_tags($c['description'] ?? ''), 65) ?>
                                </p>

                                <div class="row" style="margin:0 0 8px;">
                                    <div class="col-xs-6 text-center" style="border-right:1px solid #eee;">
                                        <strong><?= $c['participant_count'] ?></strong><br>
                                        <small class="text-muted">Joined</small>
                                    </div>
                                    <div class="col-xs-6 text-center">
                                        <strong><?= $c['completed_count'] ?></strong><br>
                                        <small class="text-muted">Completed</small>
                                    </div>
                                </div>

                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <span class="label label-<?= $c['status'] === 'Active' ? 'success' : 'default' ?>">
                                        <?= $c['status'] ?>
                                    </span>
                                    <div>
                                        <a href="<?= base_url('mentor/challenges/view/' . $c['id']) ?>"
                                           class="btn btn-xs btn-info" title="View Participants">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <?php if ($isOwn): ?>
                                        <a href="<?= base_url('mentor/challenges/edit/' . $c['id']) ?>"
                                           class="btn btn-xs btn-warning" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="<?= base_url('mentor/challenges/delete/' . $c['id']) ?>"
                                           class="btn btn-xs btn-danger" title="Delete"
                                           onclick="return confirm('Delete this challenge?')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <div class="text-center text-muted" style="padding:60px 0;">
                        <i class="fa fa-trophy fa-4x" style="opacity:.2;"></i>
                        <p style="margin-top:16px;">No challenges found. <a href="<?= base_url('mentor/challenges/create') ?>">Create the first one!</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
