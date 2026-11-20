<link href="<?= base_url();?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<div class="content">
    <style type="text/css">
        .stats-card {
            background: #fff;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            border-top: 3px solid #337ab7;
        }
        .stat-item { text-align: center; padding: 10px 0; }
        .stat-value { font-size: 28px; font-weight: bold; color: #337ab7; display: block; }
        .stat-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: .5px; margin-top: 4px; display: block; }

        .coupon-header { background: #f8f9fa; padding: 20px 24px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #337ab7; }
        .coupon-type-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-general       { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .badge-user-specific { background: #ede7f6; color: #6a1b9a; border: 1px solid #ce93d8; }

        /* Assigned clients panel */
        .assigned-clients-panel { background: #fff; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,.1); margin-bottom: 20px; }
        .assigned-clients-panel .panel-heading {
            background: linear-gradient(90deg, #6a1b9a, #8e24aa);
            color: #fff; padding: 12px 18px; border-radius: 6px 6px 0 0;
            font-size: 14px; font-weight: 600;
        }
        .assigned-clients-panel .panel-body { padding: 16px; }
        .assigned-client-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; border-radius: 6px; background: #faf5ff;
            border: 1px solid #e1bee7; margin-bottom: 8px;
        }
        .assigned-client-item:last-child { margin-bottom: 0; }
        .ac-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, #8e24aa, #6a1b9a);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; flex-shrink: 0;
        }
        .ac-info { min-width: 0; flex: 1; }
        .ac-name { font-weight: 600; font-size: 13px; color: #333; }
        .ac-sub  { font-size: 11px; color: #888; margin-top: 2px; }

        .client-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: #337ab7; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 15px; flex-shrink: 0;
        }
        .empty-state { text-align: center; padding: 50px 20px; color: #aaa; }
        .empty-state i { font-size: 48px; margin-bottom: 16px; color: #ddd; display: block; }

        .label { padding: 3px 8px; font-size: 11px; border-radius: 3px; }
        .label-success { background-color: #32c5d2; }
        .label-danger  { background-color: #e7505a; }
        .label-warning { background-color: #c49f47; }
    </style>

    <div class="page-bar"><?= $breadcrumb; ?></div>

    <div class="page-container">
        <div class="tab-pane" id="tab_1">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">

                        <div class="portlet-title clearfix">
                            <div class="caption pull-left" style="font-size:16px;line-height:34px;">
                                <?= $pagetitle ?>
                            </div>
                            <div class="pull-right">
                                <a href="<?= base_url();?>coupon" class="btn default" style="margin-top:0;">
                                    <i class="fa fa-arrow-left"></i> Back to Coupons
                                </a>
                            </div>
                        </div>

                        <div class="portlet-body">

                            <!-- ── Coupon header ───────────────────────────────────── -->
                            <div class="coupon-header">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h3 style="margin-top:0;color:#333;">
                                            <strong><?= esc($coupon->coupon_code) ?></strong>
                                            &nbsp;
                                            <?php $ctype = $coupon->coupon_type ?? 'general'; ?>
                                            <?php if ($ctype === 'user_specific'): ?>
                                                <span class="coupon-type-badge badge-user-specific"><i class="fa fa-user-lock"></i> User Specific</span>
                                            <?php else: ?>
                                                <span class="coupon-type-badge badge-general"><i class="fa fa-globe"></i> General</span>
                                            <?php endif; ?>
                                            <?php if (!empty($coupon->description)): ?>
                                                <small class="text-muted"> — <?= esc($coupon->description) ?></small>
                                            <?php endif; ?>
                                        </h3>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p><strong>Discount:</strong>
                                                    <span class="badge badge-primary">
                                                        <?php if (isset($coupon->discount_type) && $coupon->discount_type === 'percentage'): ?>
                                                            <?= number_format($coupon->discount, 1) ?>%
                                                        <?php else: ?>
                                                            ₹<?= number_format($coupon->discount, 2) ?>
                                                        <?php endif; ?>
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p><strong>Valid From:</strong> <?= date('d M Y', strtotime($coupon->valid_from)) ?></p>
                                            </div>
                                            <div class="col-md-4">
                                                <p><strong>Valid To:</strong>
                                                    <?= $coupon->valid_to ? date('d M Y', strtotime($coupon->valid_to)) : 'No expiry' ?>
                                                    <?php if ($coupon->valid_to && strtotime($coupon->valid_to) < time()): ?>
                                                        <span class="label label-warning">Expired</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <p><strong>Status:</strong>
                                            <?php if ($coupon->status == 'Active'): ?>
                                                <span class="label label-success">Active</span>
                                            <?php else: ?>
                                                <span class="label label-danger">Inactive</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>Usage Limit:</strong> <?= $coupon->usage_limit === null ? 'Unlimited' : $coupon->usage_limit ?></p>
                                        <p><strong>Per Client Limit:</strong> <?= $coupon->max_usage_per_client ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- ── User-specific: assigned clients panel ──────────── -->
                            <?php if ($ctype === 'user_specific'): ?>
                            <div class="assigned-clients-panel">
                                <div class="panel-heading">
                                    <i class="fa fa-users"></i>
                                    Assigned Clients
                                    <span style="font-size:12px;font-weight:400;margin-left:8px;opacity:.85;">
                                        (only these clients can use this coupon)
                                    </span>
                                    <span class="pull-right" style="font-size:12px;font-weight:400;">
                                        <?= count($assignedClients) ?> client<?= count($assignedClients) !== 1 ? 's' : '' ?>
                                    </span>
                                </div>
                                <div class="panel-body">
                                    <?php if (!empty($assignedClients)): ?>
                                        <div class="row">
                                            <?php foreach ($assignedClients as $ac): ?>
                                            <div class="col-md-4">
                                                <div class="assigned-client-item">
                                                    <div class="ac-avatar"><?= strtoupper(substr($ac->client_name, 0, 1)) ?></div>
                                                    <div class="ac-info">
                                                        <div class="ac-name"><?= esc($ac->client_name) ?></div>
                                                        <div class="ac-sub">
                                                            <?= esc($ac->client_email ?? '') ?>
                                                            <?php if (!empty($ac->client_mobile_number)): ?>
                                                                &nbsp;·&nbsp;<?= esc($ac->client_mobile_number) ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <a href="<?= base_url('clients/view/' . $ac->client_id) ?>" class="btn default btn-xs blue" title="View Client">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center text-muted" style="padding:20px;">
                                            <i class="fa fa-user-times fa-2x" style="color:#ddd;display:block;margin-bottom:8px;"></i>
                                            No clients assigned to this coupon.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- ── Stats cards ────────────────────────────────────── -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="stats-card" style="border-top-color:#337ab7;">
                                        <div class="stat-item">
                                            <span class="stat-value" style="color:#337ab7;"><?= $usage_stats ? $usage_stats->total_used : 0 ?></span>
                                            <span class="stat-label">Total Times Used</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-card" style="border-top-color:#32c5d2;">
                                        <div class="stat-item">
                                            <span class="stat-value" style="color:#32c5d2;"><?= $usage_stats ? $usage_stats->unique_clients : 0 ?></span>
                                            <span class="stat-label">Unique Clients</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-card" style="border-top-color:#c49f47;">
                                        <div class="stat-item">
                                            <span class="stat-value" style="color:#c49f47;">
                                                <?php if ($coupon->usage_limit === null): ?>∞
                                                <?php else: ?><?= max(0, $coupon->usage_limit - ($usage_stats ? $usage_stats->total_used : 0)) ?>
                                                <?php endif; ?>
                                            </span>
                                            <span class="stat-label">Remaining Uses</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-card" style="border-top-color:#e7505a;">
                                        <div class="stat-item">
                                            <span class="stat-value" style="color:#e7505a;">
                                                ₹<?= number_format(($usage_stats ? $usage_stats->total_used : 0) * $coupon->discount, 2) ?>
                                            </span>
                                            <span class="stat-label">Total Discount Given</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ── Usage history table ────────────────────────────── -->
                            <div class="portlet box blue-hoki">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-history"></i> Usage History
                                    </div>
                                    <?php if (!empty($usage_list)): ?>
                                    <div class="tools pull-right" style="padding-top:8px;">
                                        <div class="btn-group">
                                            <button type="button" class="btn default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                <i class="fa fa-download"></i> Export <i class="fa fa-angle-down"></i>
                                            </button>
                                            <ul class="dropdown-menu pull-right" role="menu">
                                                <li><a href="javascript:void(0)" onclick="exportCSV()"><i class="fa fa-file-excel-o"></i> Export CSV</a></li>
                                                <li><a href="javascript:void(0)" onclick="window.print()"><i class="fa fa-print"></i> Print Report</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="portlet-body">
                                    <?php if (!empty($usage_list)): ?>
                                        <table class="table table-striped table-bordered table-hover" id="usage-table">
                                            <thead>
                                                <tr>
                                                    <th width="4%">#</th>
                                                    <th width="24%">Client</th>
                                                    <th width="20%">Contact</th>
                                                    <th width="10%">Booking ID</th>
                                                    <th width="14%">Discount Applied</th>
                                                    <th width="18%">Used On</th>
                                                    <th width="10%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $counter = 1; ?>
                                                <?php foreach ($usage_list as $usage): ?>
                                                <tr>
                                                    <td><?= $counter++ ?></td>
                                                    <td>
                                                        <div style="display:flex;align-items:center;gap:10px;">
                                                            <div class="client-avatar"><?= strtoupper(substr($usage->client_name ?? '?', 0, 1)) ?></div>
                                                            <div>
                                                                <strong><?= esc($usage->client_name ?? 'Unknown') ?></strong><br>
                                                                <small class="text-muted">ID: <?= $usage->client_id ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($usage->client_email)): ?>
                                                            <i class="fa fa-envelope text-muted"></i> <?= esc($usage->client_email) ?><br>
                                                        <?php endif; ?>
                                                        <?php if (!empty($usage->client_mobile_number)): ?>
                                                            <i class="fa fa-phone text-muted"></i> <?= esc($usage->client_mobile_number) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($usage->booking_id): ?>
                                                            <span class="label label-info">#<?= $usage->booking_id ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong style="color:#2e7d32;">
                                                            ₹<?= number_format($coupon->discount, 2) ?>
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <?= date('d M Y', strtotime($usage->used_at)) ?><br>
                                                        <small class="text-muted"><?= date('h:i A', strtotime($usage->used_at)) ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if ($usage->client_id): ?>
                                                            <a href="<?= base_url('clients/view/' . $usage->client_id) ?>" class="btn default btn-xs blue" title="View Client">
                                                                <i class="fa fa-user"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($usage->booking_id): ?>
                                                            <a href="<?= base_url('booking/view/' . $usage->booking_id) ?>" class="btn default btn-xs green" title="View Booking">
                                                                <i class="fa fa-calendar"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="fa fa-ticket"></i>
                                            <h4>No Usage History</h4>
                                            <p>This coupon hasn't been used by any clients yet.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .page-bar, .portlet-title .pull-right, .btn, .dropdown-menu { display: none !important; }
    .portlet { border: none !important; box-shadow: none !important; }
    .stats-card { box-shadow: none !important; border: 1px solid #ddd !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable) {
        $('#usage-table').DataTable({
            pageLength: 25,
            order: [[5, 'desc']],
            language: {
                emptyTable: 'No usage records found',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                search: 'Search:',
                zeroRecords: 'No matching records found'
            }
        });
    }
});

function exportCSV() {
    var rows = [['Client Name','Email','Phone','Booking ID','Discount (INR)','Used On']];
    <?php foreach ($usage_list as $usage): ?>
    rows.push([
        '<?= addslashes($usage->client_name ?? '') ?>',
        '<?= addslashes($usage->client_email ?? '') ?>',
        '<?= addslashes($usage->client_mobile_number ?? '') ?>',
        '<?= $usage->booking_id ?? '' ?>',
        '<?= number_format($coupon->discount, 2) ?>',
        '<?= date('d M Y h:i A', strtotime($usage->used_at)) ?>'
    ]);
    <?php endforeach; ?>

    var csv = rows.map(function(r){ return r.map(function(c){ return '"'+String(c).replace(/"/g,'""')+'"'; }).join(','); }).join('\n');
    var a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'coupon-<?= $coupon->coupon_code ?>-<?= date('Y-m-d') ?>.csv';
    a.click();
}
</script>

<?= $this->endSection(); ?>
