<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<style>
.challenge-banner { width:100%; max-height:220px; object-fit:cover; border-radius:10px; margin-bottom:20px; }
.stat-card { background:#fff; border-radius:10px; text-align:center; padding:20px 10px; box-shadow:0 2px 8px rgba(0,0,0,.07); }
.stat-num  { font-size:28px; font-weight:700; }
.stat-lbl  { font-size:12px; color:#888; margin-top:4px; }
.badge-type { padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; }
.badge-water      { background:#e3f2fd; color:#1565c0; }
.badge-steps      { background:#e8f5e9; color:#2e7d32; }
.badge-meditation { background:#f3e5f5; color:#6a1b9a; }
.badge-sleep      { background:#fff3e0; color:#e65100; }
.badge-workout    { background:#fce4ec; color:#b71c1c; }
.badge-yoga       { background:#e0f2f1; color:#00695c; }
.badge-custom     { background:#eeeeee; color:#424242; }
.avatar { width:36px; height:36px; border-radius:50%; object-fit:cover; }
.avatar-placeholder { width:36px; height:36px; border-radius:50%; background:#e0e0e0; display:inline-flex; align-items:center; justify-content:center; color:#999; }
</style>

<div class="row">
    <div class="col-md-4">
        <div class="portlet light bordered">
            <div class="portlet-body">
                <?php if (!empty($challenge['image_url'])): ?>
                    <img src="<?= base_url($challenge['image_url']) ?>" class="challenge-banner" alt="">
                <?php endif; ?>

                <h3 style="margin-top:0;"><?= esc($challenge['title']) ?></h3>
                <p>
                    <span class="badge-type badge-<?= strtolower($challenge['activity_type']) ?>">
                        <?= ucfirst($challenge['activity_type']) ?>
                    </span>
                    &nbsp;
                    <span class="label label-<?= $challenge['status'] === 'Active' ? 'success' : 'default' ?>">
                        <?= $challenge['status'] ?>
                    </span>
                    <?php if ($challenge['created_by'] == $mentor_id && $challenge['creator_type'] === 'Mentor'): ?>
                        <span class="label label-warning">My Challenge</span>
                    <?php endif; ?>
                </p>

                <p class="text-muted"><?= nl2br(esc($challenge['description'] ?? '')) ?></p>

                <table class="table table-condensed" style="font-size:13px;">
                    <tr><td><strong>Daily Target</strong></td><td><?= $challenge['target_value'] ?> <?= esc($challenge['target_unit'] ?? '') ?></td></tr>
                    <tr><td><strong>Streak Days</strong></td><td><?= $challenge['target_days'] ?> days</td></tr>
                    <tr><td><strong>Reward Points</strong></td><td><?= $challenge['points'] ?> pts</td></tr>
                    <tr><td><strong>Reward Type</strong></td><td><?= ucwords(str_replace('_', ' ', $challenge['reward_type'])) ?></td></tr>
                    <tr><td><strong>Created By</strong></td><td><?= esc($challenge['creator_type']) ?></td></tr>
                </table>

                <?php if ($challenge['created_by'] == $mentor_id && $challenge['creator_type'] === 'Mentor'): ?>
                <a href="<?= base_url('mentor/challenges/edit/' . $challenge['id']) ?>" class="btn btn-warning btn-sm btn-block">
                    <i class="fa fa-pencil"></i> Edit Challenge
                </a>
                <?php endif; ?>
                <a href="<?= base_url('mentor/challenges') ?>" class="btn btn-default btn-sm btn-block" style="margin-top:6px;">
                    <i class="fa fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <?php
        $total     = count($participants);
        $completed = count(array_filter($participants, fn($p) => $p['status'] === 'Completed'));
        $active    = count(array_filter($participants, fn($p) => $p['status'] === 'Joined'));
        ?>
        <div class="row" style="margin-bottom:16px;">
            <div class="col-xs-4"><div class="stat-card"><div class="stat-num text-primary"><?= $total ?></div><div class="stat-lbl">Total Joined</div></div></div>
            <div class="col-xs-4"><div class="stat-card"><div class="stat-num text-success"><?= $completed ?></div><div class="stat-lbl">Completed</div></div></div>
            <div class="col-xs-4"><div class="stat-card"><div class="stat-num text-warning"><?= $active ?></div><div class="stat-lbl">In Progress</div></div></div>
        </div>

        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption"><i class="fa fa-users"></i> <span class="caption-subject bold">Participants &amp; Progress</span></div>
            </div>
            <div class="portlet-body">
                <?php if (!empty($participants)): ?>
                <div class="table-responsive">
                    <table class="table table-hover" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th style="width:160px;">Progress</th>
                                <th>Days Done</th>
                                <th>Joined On</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($participants as $p): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($p['client_profile_pic'])): ?>
                                        <img src="<?= base_url('uploads/client/' . $p['client_profile_pic']) ?>" class="avatar" alt="">
                                    <?php else: ?>
                                        <span class="avatar-placeholder"><i class="fa fa-user" style="font-size:14px;"></i></span>
                                    <?php endif; ?>
                                    &nbsp;<?= esc($p['client_name']) ?><br>
                                    <small class="text-muted"><?= esc($p['client_mobile_number']) ?></small>
                                </td>
                                <td>
                                    <?php $sc = ['Completed'=>'success','Joined'=>'warning','Failed'=>'danger','Abandoned'=>'default']; ?>
                                    <span class="label label-<?= $sc[$p['status']] ?? 'default' ?>"><?= $p['status'] ?></span>
                                </td>
                                <td>
                                    <div class="progress" style="margin:0;height:8px;background:#eee;border-radius:4px;">
                                        <div class="progress-bar progress-bar-<?= $p['status'] === 'Completed' ? 'success' : 'warning' ?>"
                                             style="width:<?= min(100, $p['progress_percent']) ?>%;border-radius:4px;"></div>
                                    </div>
                                    <small><?= $p['current_streak'] ?>/<?= $p['target_days'] ?> days (<?= $p['progress_percent'] ?>%)</small>
                                </td>
                                <td><?= $p['total_days_completed'] ?></td>
                                <td><?= date('d M Y', strtotime($p['started_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="text-center text-muted" style="padding:40px 0;">
                        <i class="fa fa-users fa-3x" style="opacity:.2;"></i>
                        <p style="margin-top:12px;">No users have joined this challenge yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
