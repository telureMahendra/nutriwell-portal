<?php
$db = db_connect();

$target_weight = 0;
$height = 0;
$height_in = '';
$weight = 0;
$clientSql = "SELECT * from client_profile WHERE client_id=" . $client_id . "";
$clientQuery = $db->query($clientSql);
$clientRow = $clientQuery->getRowArray();
if ($clientRow) {
    $target_weight = $clientRow["target_weight"];
    $height        = $clientRow["height"];
    $height_in     = $clientRow["height_in"];
    $weight        = $clientRow["weight"];
} else {
    die('No client data found');
}

$searchdate = date('Y-m-d');
if (isset($_GET["searchdate"])) {
    $searchdate = $_GET["searchdate"];
}

$targetGlasses = 16;
$totalGlasses  = 0;
$sqlA    = "SELECT quantity as totalGlasses from water WHERE client_id=" . $client_id . " AND DATE(date)='" . $searchdate . "'";
$resultA = $db->query($sqlA)->getRowArray();
if ($resultA) {
    $totalGlasses = $resultA["totalGlasses"];
}

function calculateBMI($weight_kg, $height, $unit = 'cm')
{
    switch ($unit) {
        case 'ft':
            $parts    = explode(".", $height);
            $feet     = (int) $parts[0];
            $inches   = isset($parts[1]) ? (int) $parts[1] : 0;
            $height_m = ((($feet * 12) + $inches) * 2.54) / 100;
            break;
        case 'cm':
        default:
            $height_m = $height / 100;
    }
    if ($height_m <= 0) return 0;
    return round($weight_kg / ($height_m * $height_m), 2);
}

function getTargetCalories($bmi, $weight)
{
    if ((18.5 < $bmi) && ($bmi < 22.9))      $total = $weight * 30;
    elseif ((23 < $bmi) && ($bmi < 24.9))    $total = $weight * 25;
    elseif ($bmi > 25)                        $total = $weight * 20;
    else                                       $total = 63 * 21;
    return ($total == 0) ? 1 : $total;
}

$totalCalorie = 0;
$calorieRow   = $db->query("SELECT sum(quantity) as totalCalorie from calorie WHERE client_id=" . $client_id . " AND DATE(date)='" . $searchdate . "'")->getRowArray();
if ($calorieRow) {
    $totalCalorie = round($calorieRow["totalCalorie"], 2);
}

$bmi               = (strtolower(trim($height_in)) == 'cm') ? calculateBMI($weight, $height) : calculateBMI($weight, $height, 'ft');
$totalTargetCalories = getTargetCalories($bmi, $weight);

$totalSteps = 0;
$resultS2   = $db->query("SELECT data_count as totalSteps from steps_calories WHERE client_id=" . $client_id . " AND DATE(count_date)='" . $searchdate . "' AND count_type=0")->getRowArray();
if ($resultS2) $totalSteps = round($resultS2["totalSteps"], 0);

$totalBurnedCalories = 0;
$resultS1 = $db->query("SELECT data_count as totalCalories from steps_calories WHERE client_id=" . $client_id . " AND DATE(count_date)='" . $searchdate . "' AND count_type=1")->getRowArray();
if ($resultS1) $totalBurnedCalories = round($resultS1["totalCalories"], 0);

// Load current targets
$targetRow      = $db->table('client_profile')->select('target_water, target_sleep, target_weight')->where('client_id', $client_id)->get()->getRow();
$cur_target_water   = $targetRow ? (int)$targetRow->target_water               : 8;
$cur_target_sleep   = $targetRow ? (int)$targetRow->target_sleep               : 480;
$cur_target_weight  = $targetRow ? (float)($targetRow->target_weight ?? 0)     : 0;
$stepsTargetModel   = model('App\Models\StepsTargetModel');
$calorieTargetModel = model('App\Models\CaloriesTargetModel');
$cur_target_steps   = $stepsTargetModel->getCurrentTarget($client_id);
$cur_target_calorie = $calorieTargetModel->getCurrentTarget($client_id, null, 'intake');
?>
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>

<style>
/* ── Page Header ───────────────────────────────────────────── */
.cd-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}
.cd-header .cd-title {
    font-size: 22px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}
.cd-header .cd-client {
    font-size: 18px;
    font-weight: 600;
    color: #34495e;
    margin: 0;
}
.cd-header .cd-client small {
    font-size: 13px;
    font-weight: 400;
    color: #7f8c8d;
}

/* ── Date Filter Bar ──────────────────────────────────────── */
.cd-filter-bar {
    background: #fff;
    border-radius: 10px;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
}
.cd-filter-bar .form-control {
    max-width: 180px;
    border-radius: 6px;
    border: 1px solid #dde3ec;
    font-weight: 500;
    color: #2c3e50;
}

/* ── Metric Cards ─────────────────────────────────────────── */
.metric-cards-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}
.metric-card {
    flex: 1 1 calc(33.333% - 16px);
    min-width: 200px;
    border-radius: 14px;
    padding: 20px 20px 16px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0,0,0,.13);
    transition: transform .2s, box-shadow .2s;
    color: #fff;
}
.metric-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(0,0,0,.18);
}
.metric-card .mc-icon {
    position: absolute;
    right: -10px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .15;
    width: 90px;
    height: 90px;
    object-fit: contain;
    pointer-events: none;
}
.metric-card .mc-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    opacity: .85;
    margin-bottom: 6px;
}
.metric-card .mc-value {
    font-size: 32px;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 4px;
    text-shadow: 0 2px 6px rgba(0,0,0,.2);
}
.metric-card .mc-target {
    font-size: 13px;
    opacity: .8;
    margin-bottom: 10px;
}
.metric-card .mc-bar-wrap {
    background: rgba(255,255,255,.25);
    border-radius: 20px;
    height: 6px;
    overflow: hidden;
}
.metric-card .mc-bar {
    height: 100%;
    border-radius: 20px;
    background: rgba(255,255,255,.85);
    transition: width .6s ease;
    min-width: 4px;
}

/* Card colour variants */
.mc-water   { background: linear-gradient(135deg, #0cc2f0, #0291c1); }
.mc-calorie { background: linear-gradient(135deg, #a855f7, #7c22c7); }
.mc-weight  { background: linear-gradient(135deg, #f97060, #d94535); }
.mc-bmi     { background: linear-gradient(135deg, #6472f5, #3a47d5); }
.mc-steps   { background: linear-gradient(135deg, #f0a800, #c47f00); }
.mc-burned  { background: linear-gradient(135deg, #22c55e, #168c40); }

/* ── Set Daily Targets ──────────────────────────────────────── */
.targets-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(0,0,0,.09);
    overflow: hidden;
    margin-top: 8px;
}
.targets-card .tc-header {
    background: linear-gradient(90deg, #e74c3c, #c0392b);
    color: #fff;
    padding: 14px 22px;
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.targets-card .tc-body {
    padding: 22px;
}
.targets-card .form-group label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 13px;
}
.targets-card .form-control {
    border-radius: 8px;
    border: 1.5px solid #e0e6ed;
    font-size: 15px;
    font-weight: 600;
    color: #2c3e50;
    transition: border-color .2s;
}
.targets-card .form-control:focus {
    border-color: #e74c3c;
    box-shadow: 0 0 0 3px rgba(231,76,60,.12);
}
.targets-card .btn-save {
    background: linear-gradient(135deg, #27ae60, #1e8449);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    padding: 11px 0;
    width: 100%;
    transition: opacity .2s, transform .2s;
}
.targets-card .btn-save:hover {
    opacity: .9;
    transform: translateY(-1px);
}
</style>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li><i class="fa fa-home"></i><a href="<?= base_url('mentor/appointment'); ?>">Home</a><i class="fa fa-angle-right"></i></li>
        <li><a href="<?= base_url('mentor'); ?>">Dashboard</a></li>
    </ul>
</div>

<!-- Page Header -->
<div class="cd-header">
    <h3 class="cd-title"><i class="fa fa-tachometer" style="color:#e74c3c;margin-right:8px;"></i>Client Dashboard</h3>
    <h4 class="cd-client">
        <?= ucwords(strtolower($clientRow["client_name"])); ?>
        <small>(<?= trim($clientRow["client_email"]); ?>)</small>
    </h4>
</div>

<!-- Date Filter -->
<?php
$attributes = ['method' => 'get', 'class' => 'login-form', 'id' => 'loginform'];
echo form_open('mentor/appointment/clientdashboard/' . $client_id, $attributes);
?>
<div class="cd-filter-bar">
    <input type="date" class="form-control" name="searchdate" id="searchdate" required value="<?= $searchdate; ?>">
    <button type="submit" name="submit" value="Filter" class="btn green" style="border-radius:6px;">
        <i class="fa fa-search"></i> Search
    </button>
</div>
<?= form_close(); ?>

<!-- Metric Cards -->
<?php
// Helper: compute percentage for progress bar, capped 0-100
function pct($current, $target) {
    if ($target <= 0) return 0;
    return min(100, round(($current / $target) * 100));
}
?>
<div class="metric-cards-row">

    <!-- WATER -->
    <div class="metric-card mc-water" data-toggle="modal" data-target="#waterGraphModal" style="cursor: pointer;">
        <img class="mc-icon" src="<?= base_url('public/assets/admin/pages/img/water.png'); ?>">
        <div class="mc-label">Water</div>
        <div class="mc-value"><?= $totalGlasses; ?></div>
        <div class="mc-target">/ <?= $cur_target_water; ?> Glasses</div>
        <div class="mc-bar-wrap"><div class="mc-bar" style="width:<?= pct($totalGlasses, $cur_target_water); ?>%"></div></div>
    </div>

    <!-- CALORIE INTAKE -->
    <div class="metric-card mc-calorie" data-toggle="modal" data-target="#calorieGraphModal" style="cursor: pointer;">
        <img class="mc-icon" src="<?= base_url('public/assets/admin/pages/img/Calorie.png'); ?>">
        <div class="mc-label">Calorie Intake</div>
        <div class="mc-value"><?= $totalCalorie; ?></div>
        <div class="mc-target">/ <?= $cur_target_calorie ?: $totalTargetCalories; ?> kcal</div>
        <div class="mc-bar-wrap"><div class="mc-bar" style="width:<?= pct($totalCalorie, $cur_target_calorie ?: $totalTargetCalories); ?>%"></div></div>
    </div>

    <!-- WEIGHT -->
    <div class="metric-card mc-weight" data-toggle="modal" data-target="#weightGraphModal" style="cursor: pointer;">
        <img class="mc-icon" src="<?= base_url('public/assets/admin/pages/img/weight.png'); ?>">
        <div class="mc-label">Weight</div>
        <div class="mc-value"><?= $weight; ?></div>
        <div class="mc-target">Target: <?= $cur_target_weight; ?> kg</div>
        <div class="mc-bar-wrap"><div class="mc-bar" style="width:<?= pct($weight, max($weight, $cur_target_weight)); ?>%"></div></div>
    </div>

    <!-- BMI -->
    <div class="metric-card mc-bmi" data-toggle="modal" data-target="#bmiGraphModal" style="cursor: pointer;">
        <img class="mc-icon" src="<?= base_url('public/assets/admin/pages/img/bmi.png'); ?>">
        <div class="mc-label">BMI</div>
        <div class="mc-value"><?= $bmi; ?></div>
        <div class="mc-target">
            <?php
            $bmiVal = (float)$bmi;
            if ($bmiVal < 18.5)           echo 'Underweight';
            elseif ($bmiVal < 25)         echo 'Normal weight';
            elseif ($bmiVal < 30)         echo 'Overweight';
            else                          echo 'Obese';
            ?>
        </div>
        <div class="mc-bar-wrap">
            <div class="mc-bar" style="width:<?= min(100, round(($bmiVal / 40) * 100)); ?>%"></div>
        </div>
    </div>

    <!-- STEPS -->
    <div class="metric-card mc-steps" data-toggle="modal" data-target="#stepsGraphModal" style="cursor: pointer;">
        <img class="mc-icon" src="<?= base_url('public/assets/admin/pages/img/steps.png'); ?>">
        <div class="mc-label">Steps</div>
        <div class="mc-value"><?= number_format($totalSteps); ?></div>
        <div class="mc-target">/ <?= number_format($cur_target_steps); ?> steps</div>
        <div class="mc-bar-wrap"><div class="mc-bar" style="width:<?= pct($totalSteps, $cur_target_steps); ?>%"></div></div>
    </div>

    <!-- CALORIES BURNED -->
    <div class="metric-card mc-burned" data-toggle="modal" data-target="#burnedGraphModal" style="cursor: pointer;">
        <img class="mc-icon" src="<?= base_url('public/assets/admin/pages/img/burned.png'); ?>">
        <div class="mc-label">Calories Burned</div>
        <div class="mc-value"><?= $totalBurnedCalories; ?></div>
        <div class="mc-target">kcal today</div>
        <div class="mc-bar-wrap"><div class="mc-bar" style="width:<?= pct($totalBurnedCalories, max(1, $totalBurnedCalories)); ?>%"></div></div>
    </div>

</div>

<!-- Set Daily Targets -->
<div class="targets-card">
    <div class="tc-header">
        <i class="fa fa-bullseye"></i> Set Daily Targets
    </div>
    <div class="tc-body">
        <?php if (isset($_GET['targets_saved'])): ?>
            <div class="alert alert-success"><i class="fa fa-check-circle"></i> Targets updated successfully!</div>
        <?php endif; ?>
        <form method="post" action="<?= base_url('mentor/appointment/set_client_targets/' . $client_id); ?>">
            <?= csrf_field(); ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fa fa-tint" style="color:#0cc2f0;"></i> Water Target <small class="text-muted">(glasses/day)</small></label>
                        <input type="number" class="form-control" name="target_water" value="<?= $cur_target_water; ?>" min="1" max="30" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fa fa-moon-o" style="color:#6472f5;"></i> Sleep Target <small class="text-muted">(minutes/day)</small></label>
                        <input type="number" class="form-control" name="target_sleep" value="<?= $cur_target_sleep; ?>" min="60" max="900" required>
                        <small class="text-muted">e.g. 480 = 8 hrs</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fa fa-fire" style="color:#a855f7;"></i> Calorie Intake Target <small class="text-muted">(kcal/day)</small></label>
                        <input type="number" class="form-control" name="target_calorie" value="<?= $cur_target_calorie; ?>" min="500" max="6000" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fa fa-road" style="color:#f0a800;"></i> Steps Target <small class="text-muted">(steps/day)</small></label>
                        <input type="number" class="form-control" name="target_steps" value="<?= $cur_target_steps; ?>" min="500" max="50000" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fa fa-balance-scale" style="color:#f97060;"></i> Weight Target <small class="text-muted">(kg)</small></label>
                        <input type="number" step="0.1" class="form-control" name="target_weight" value="<?= $cur_target_weight; ?>" min="20" max="300" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group" style="margin-top:22px;">
                        <button type="submit" class="btn-save">
                            <i class="fa fa-save"></i> Save Targets
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- Include All Modals (HTML and scripts after jQuery and libraries) -->
<?= $this->include('admin/clients/partials/modals'); ?>
<?= $this->endSection() ?>
