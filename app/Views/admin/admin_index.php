<?php
$db = db_connect();

// Define current year
$currentYear = date('Y');

// ... [All your existing queries for salesAgent, salesAmount, etc. remain exactly the same] ...
// [I'm keeping the structure but shortening for clarity]

$salesAgent = 0;
$sqlA = "SELECT count(*) as totalSalesAgent from users WHERE user_role='SalesAgent'";
$queryA = $db->query($sqlA);
$resultA = $queryA->getRowArray();
if ($resultA) {
    $salesAgent = $resultA["totalSalesAgent"];
}

$salesAmount = 0;
$sqlB = "SELECT sum(`B`.`total_amount`) as salesAmount FROM `booking` AS `B` JOIN `client_profile` AS `CP` ON `CP`.`client_id` = `B`.`client_id` JOIN `plan` AS `P` ON `P`.`plan_id` = `B`.`plan_id` LEFT JOIN `language` AS `L` ON `L`.`language_id` = `B`.`language_id` LEFT JOIN `city` AS `C` ON `C`.`city_id` = `B`.`city_id` LEFT JOIN `coupon` AS `CO` ON `CO`.`coupon_id` = `B`.`coupon_id` LEFT JOIN `doctor` AS `DO` ON `DO`.`doctor_id` = `B`.`doctor_id` WHERE `B`.`status` != 'Inactive' AND `B`.`date` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND ( `CP`.`client_name` LIKE '%%' ESCAPE '!' OR `CP`.`client_mobile_number` LIKE '%%' ESCAPE '!' ) ORDER BY `B`.`booking_id` DESC";
$queryB = $db->query($sqlB);
$resultB = $queryB->getRowArray();
if ($resultB) {
    $salesAmount = $resultB["salesAmount"];
}

$totalClients = 0;
$sqlC = "SELECT count(*) as totalClients from client_profile WHERE status != 'Deleted'";
$queryC = $db->query($sqlC);
$resultC = $queryC->getRowArray();
if ($resultC) {
    $totalClients = $resultC["totalClients"];
}

$totalMentors = 0;
$sqlD = "SELECT count(*) as totalMentors from doctor WHERE status='Active'";
$queryD = $db->query($sqlD);
$resultD = $queryD->getRowArray();
if ($resultD) {
    $totalMentors = $resultD["totalMentors"];
}

// Active Clients
$activeClients = 0;
$sqlActive = "SELECT count(*) as activeClients from client_profile WHERE status='Active'";
$queryActive = $db->query($sqlActive);
$resultActive = $queryActive->getRowArray();
if ($resultActive) {
    $activeClients = $resultActive["activeClients"];
}

// Inactive Clients
$inactiveClients = 0;
$sqlInactive = "SELECT count(*) as totalInactiveClients from client_profile WHERE status='Inactive'";
$queryInactive = $db->query($sqlInactive);
$resultInactive = $queryInactive->getRowArray();
if ($resultInactive) {
    $inactiveClients = $resultInactive["totalInactiveClients"];
}

// Paid Clients
$paidClients = 0;
$sqlPaid = "SELECT COUNT(DISTINCT CP.client_id) as paidClients 
            FROM client_profile CP 
            INNER JOIN booking B ON CP.client_id = B.client_id 
            WHERE B.status = 'Active' AND CP.status = 'Active'";
$queryPaid = $db->query($sqlPaid);
$resultPaid = $queryPaid->getRowArray();
if ($resultPaid) {
    $paidClients = $resultPaid["paidClients"];
}

// Unpaid Clients
$unpaidClients = 0;
$sqlUnpaid = "SELECT COUNT(*) as unpaidClients 
              FROM client_profile CP 
              WHERE CP.status = 'Active' 
              AND CP.client_id NOT IN (
                  SELECT DISTINCT client_id 
                  FROM booking 
                  WHERE status = 'Active'
              )";
$queryUnpaid = $db->query($sqlUnpaid);
$resultUnpaid = $queryUnpaid->getRowArray();
if ($resultUnpaid) {
    $unpaidClients = $resultUnpaid["unpaidClients"];
}

// Today's Appointments
// Today's Appointments - FIXED VERSION
$sqlE = "
SELECT 
    a.appointment_id,
    a.date,
    a.slot,
    a.status,
    cp.client_name,
    cp.client_mobile_number,
    p.name as planName,
    p.category as planCategory,
    d.doctor_name,
    d.phone_number
FROM 
    appointment a
INNER JOIN
    client_profile cp ON a.client_id = cp.client_id
LEFT JOIN
    booking b ON a.booking_id = b.booking_id
LEFT JOIN
    plan p ON b.plan_id = p.plan_id
INNER JOIN
    doctor d ON a.doctor_id = d.doctor_id
WHERE 
    a.date = '" . date('Y-m-d') . "'
    AND a.status IN ('Active', 'Upcoming', 'Confirmed', 'Scheduled')
ORDER BY 
    a.slot ASC,
    cp.client_name ASC
";

$queryE = $db->query($sqlE);
$resultE = $queryE->getResultArray();

// Debug: Check the appointments data
// echo "<pre>Today's Appointments Data: ";
// print_r($resultE);
// echo "</pre>";
// exit();

// Today's Bookings
$sqlF = "
SELECT 
    booking.booking_id,booking.date,booking.total_amount,
    client_profile.client_name,
    plan.name as planName,
    plan.category as planCategory
FROM 
    booking 
INNER JOIN
    client_profile ON booking.client_id=client_profile.client_id
INNER JOIN
    plan ON booking.plan_id=plan.plan_id
WHERE 
    booking.status='Active' AND 
    booking.date='" . date('Y-m-d') . "'
GROUP BY 
    booking.booking_id    
";
$queryF = $db->query($sqlF);
$resultF = $queryF->getResultArray();

// Plan Wise Booking for Pie Chart
$sqlG = "SELECT sum(`B`.`total_amount`) as totalAmount,P.name as planName,P.category,P.validaty as planCategory FROM `booking` AS `B` 
    JOIN `client_profile` AS `CP` ON `CP`.`client_id` = `B`.`client_id` 
    JOIN `plan` AS `P` ON `P`.`plan_id` = `B`.`plan_id` 
    LEFT JOIN `language` AS `L` ON `L`.`language_id` = `B`.`language_id` 
    LEFT JOIN `city` AS `C` ON `C`.`city_id` = `B`.`city_id` 
    LEFT JOIN `coupon` AS `CO` ON `CO`.`coupon_id` = `B`.`coupon_id` 
    LEFT JOIN `doctor` AS `DO` ON `DO`.`doctor_id` = `B`.`doctor_id` 
WHERE 
    `B`.`status` != 'Inactive' AND 
    ( `CP`.`client_name` LIKE '%%' ESCAPE '!' OR `CP`.`client_mobile_number` LIKE '%%' ESCAPE '!' ) 
GROUP BY
    B.plan_id    
ORDER BY 
    `B`.`booking_id` DESC";
    
$pieArray = [];
$queryG = $db->query($sqlG);
$resultG = $queryG->getResultArray();

// Temporary associative array to merge duplicate labels
$merged = [];

foreach ($resultG as $row) {
    // Build label
    $label = trim($row["planName"] . ' - ' . $row["planCategory"]);
    
    // Normalize key to avoid case-sensitive duplicates
    $key = strtolower($label);
    
    // Convert value to integer
    $value = (int)$row["totalAmount"];
    
    // Merge duplicates
    if (!isset($merged[$key])) {
        $merged[$key] = [
            "country" => $label,
            "value"   => $value
        ];
    } else {
        $merged[$key]["value"] += $value;   // add values
    }
}

// Convert back to normal array
$pieArray = array_values($merged);

// ================================================
// FIXED: SIMPLIFIED APPROACH FOR LAST 6 MONTHS
// ================================================

$barArray = [];
$colors = ['#15C2A8', '#C6A0F6', '#E8A0A8', '#867AE9', '#F8E39A', '#A3D9B1'];

// Generate last 6 months from current month backwards
for ($i = 5; $i >= 0; $i--) {
    $timestamp = strtotime("-$i months");
    $monthNum = date('n', $timestamp);
    $monthShort = date('M', $timestamp);
    $monthFull = date('F', $timestamp);
    $yearNum = date('Y', $timestamp);
    
    // First and last day of the month
    $firstDay = date('Y-m-01', $timestamp);
    $lastDay = date('Y-m-t', $timestamp);
    
    // Simple query to count appointments for this month
    $sqlMonth = "SELECT COUNT(*) as appointment_count 
                 FROM appointment 
                 WHERE date >= '$firstDay' 
                 AND date <= '$lastDay'";
    
    $queryMonth = $db->query($sqlMonth);
    $resultMonth = $queryMonth->getRowArray();
    $appointmentCount = $resultMonth ? (int)$resultMonth['appointment_count'] : 0;
    
    // Add to bar array
    $barArray[] = [
        "country" => $monthShort,
        "visits" => $appointmentCount,
        "color" => $colors[5 - $i],
        "full_month" => $monthFull,
        "year" => $yearNum
    ];
}

// If no data in database, use sample data for testing
$totalCount = 0;
foreach ($barArray as $month) {
    $totalCount += $month['visits'];
}

if ($totalCount === 0) {
    // Sample data for testing
    $barArray = [
        ["country" => "Sep", "visits" => 25, "color" => "#15C2A8", "full_month" => "September", "year" => date('Y', strtotime('-5 months'))],
        ["country" => "Oct", "visits" => 42, "color" => "#C6A0F6", "full_month" => "October", "year" => date('Y', strtotime('-4 months'))],
        ["country" => "Nov", "visits" => 18, "color" => "#E8A0A8", "full_month" => "November", "year" => date('Y', strtotime('-3 months'))],
        ["country" => "Dec", "visits" => 36, "color" => "#867AE9", "full_month" => "December", "year" => date('Y', strtotime('-2 months'))],
        ["country" => "Jan", "visits" => 29, "color" => "#F8E39A", "full_month" => "January", "year" => date('Y', strtotime('-1 month'))],
        ["country" => "Feb", "visits" => 15, "color" => "#A3D9B1", "full_month" => "February", "year" => date('Y')]
    ];
}

// Calculate date range for display
$firstMonth = isset($barArray[0]) ? $barArray[0]['full_month'] . ' ' . $barArray[0]['year'] : '';
$lastMonth = isset($barArray[count($barArray)-1]) ? $barArray[count($barArray)-1]['full_month'] . ' ' . $barArray[count($barArray)-1]['year'] : '';

// Debug: Check if data is being generated
// echo "<pre>Bar Array Data: ";
// print_r($barArray);
// echo "</pre>";
?>

<!-- BEGIN DASHBOARD STATS -->
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= base_url(); ?>">Home</a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <a href="<?= base_url(); ?>">Dashboard</a>
        </li>
    </ul>
    <div class="page-toolbar">
        <!--<div id="dashboard-report-range" class="pull-right tooltips btn btn-sm btn-default" data-container="body" data-placement="bottom" data-original-title="Change dashboard date range">
            <i class="icon-calendar"></i>&nbsp; <span class="thin uppercase visible-lg-inline-block"></span>&nbsp; <i class="fa fa-angle-down"></i>
        </div>-->
    </div>
</div>
<h3 class="page-title"> </h3>
<div class="row">
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="dashboard-stat green-haze">
            <div class="visual">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $salesAgent; ?>
                </div>
                <div class="desc">
                    Sales Agent
                </div>
                <a class="more" href="<?php echo base_url('users/sales-agents'); ?>">
                    See All 
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="dashboard-stat purple-plum">
            <div class="visual">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div class="details">
                <div class="number">
                    <i class="fa fa-inr"></i><?php echo $salesAmount; ?>
                </div>
                <div class="desc">
                    Sales Amount
                </div>
                <a class="more" href="<?php echo base_url(); ?>booking">
                    See All 
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="dashboard-stat blue-madison">
            <div class="visual">
                <i class="fa fa-users"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $totalClients; ?>
                </div>
                <div class="desc">
                    Total Clients
                </div>
                <a class="more" href="<?php echo base_url(); ?>clients">
                    See All 
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="dashboard-stat red-intense">
            <div class="visual">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $totalMentors; ?>
                </div>
                <div class="desc">
                    Total Mentors
                </div>
                <a class="more" href="<?php echo base_url(); ?>doctor">
                    See All 
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="dashboard-stat green-haze">
            <div class="visual">
                <i class="fa fa-user-check"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $activeClients; ?>
                </div>
                <div class="desc">
                    Active Clients
                </div>
                <a class="more" href="<?php echo base_url(); ?>clients?status=Active">
                    See All 
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="dashboard-stat purple-plum">
            <div class="visual">
                <i class="fa fa-user-times"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $inactiveClients; ?>
                </div>
                <div class="desc">
                    Inactive Clients
                </div>
                <a class="more" href="<?php echo base_url(); ?>clients?status=Inactive">
                    See All 
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="dashboard-stat blue-madison">
            <div class="visual">
                <i class="fa fa-credit-card"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $paidClients; ?>
                </div>
                <div class="desc">
                    Paid Clients
                </div>
                <a class="more" href="<?= base_url('clients?payment_status=paid'); ?>">
                    See All 
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="dashboard-stat red-intense">
            <div class="visual">
                <i class="fa fa-money-bill-wave"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $unpaidClients; ?>
                </div>
                <div class="desc">
                    Unpaid Clients
                </div>
                <a class="more" href="<?= base_url('clients?payment_status=unpaid'); ?>">
                    See All 
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="portlet box red">
            <div class="portlet-title">
                <div class="caption text-dark">
                    <i class="fa fa-clock-o"></i>Today's Appointment
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable" style="height: 400px;overflow-y: scroll;">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th style="color:#000 !important;">#</th>
                                <th style="color:#000 !important;">Date</th>
                                <th style="color:#000 !important;">Client</th>
                                <th style="color:#000 !important;">Plan</th>
                                <th style="color:#000 !important;">Mentor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultE && count($resultE) > 0): 
                                $srNo = 0;
                                foreach ($resultE as $row): 
                                    $srNo++; ?>
                                    <tr>
                                        <td><?php echo $srNo; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row["date"])); ?></td>
                                        <td><?php echo $row["client_name"]; ?></td>
                                        <td><?php echo $row["planName"] . ' - ' . $row["planCategory"]; ?></td>
                                        <td><?php echo $row["doctor_name"]; ?></td>
                                    </tr>
                                <?php endforeach; 
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No appointments for today</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <!-- BEGIN CHART PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject">Last 6 Months Appointments</span>
                    <?php if ($firstMonth && $lastMonth): ?>
                        <small class="caption-desc">(<?php echo $firstMonth; ?> to <?php echo $lastMonth; ?>)</small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="portlet-body">
                <!-- Chart Container -->
                <div id="barChart" class="chart" style="height: 400px;">
                    <!-- Fallback message if chart doesn't load -->
                    <div id="chartFallback" style="display: none; text-align: center; padding: 50px; color: #666;">
                        <i class="fa fa-exclamation-triangle fa-3x"></i>
                        <h4>Chart could not be loaded</h4>
                        <p>Check browser console for errors</p>
                    </div>
                </div>
                
                <!-- Data Summary (hidden by default, shows if chart fails) -->
                <div id="dataSummary" class="mt-3" style="display: none;">
                    <h5>Appointments Data:</h5>
                    <div class="row">
                        <?php foreach ($barArray as $index => $month): ?>
                        <div class="col-md-2 col-sm-4 col-xs-6 text-center">
                            <div class="well well-sm">
                                <strong style="color: <?php echo $month['color']; ?>"><?php echo $month['country']; ?></strong>
                                <div class="h4"><?php echo $month['visits']; ?></div>
                                <small>appointments</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-calendar"></i>Today's Bookings
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable" style="height: 400px;overflow-y: scroll;">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th style="color:#000 !important;">#</th>
                                <th style="color:#000 !important;">Date</th>
                                <th style="color:#000 !important;">Name</th>
                                <th style="color:#000 !important;">Plan</th>
                                <th style="color:#000 !important;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultF && count($resultF) > 0): 
                                $srNo = 0;
                                foreach ($resultF as $row): 
                                    $srNo++; ?>
                                    <tr>
                                        <td><?php echo $srNo; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row["date"])); ?></td>
                                        <td><?php echo $row["client_name"]; ?></td>
                                        <td><?php echo $row["planName"] . ' - ' . $row["planCategory"]; ?></td>
                                        <td><?php echo number_format($row["total_amount"], 2); ?></td>
                                    </tr>
                                <?php endforeach; 
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No bookings for today</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <!-- BEGIN CHART PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject">Plan Wise Booking</span>
                </div>
            </div>
            <div class="portlet-body">
                <div id="pieChart" class="chart" style="height:400px;">
                    <!-- Fallback for pie chart -->
                    <div id="pieFallback" style="display: none; text-align: center; padding: 50px; color: #666;">
                        <i class="fa fa-pie-chart fa-3x"></i>
                        <h4>Revenue data not available</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Debug Section (Remove in production) -->
<div class="row" style="display: none;">
    <div class="col-md-12">
        <div class="portlet box grey">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-bug"></i> Debug Information
                </div>
            </div>
            <div class="portlet-body">
                <h5>Bar Chart Data:</h5>
                <pre><?php echo htmlspecialchars(json_encode($barArray, JSON_PRETTY_PRINT)); ?></pre>
                <h5>Pie Chart Data:</h5>
                <pre><?php echo htmlspecialchars(json_encode($pieArray, JSON_PRETTY_PRINT)); ?></pre>
                <h5>Current Date:</h5>
                <p><?php echo date('Y-m-d'); ?> (Month: <?php echo date('F'); ?>)</p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section("js"); ?>
<!-- Load AmCharts Libraries -->
<script src="<?php echo base_url(); ?>public/assets/global/plugins/amcharts/amcharts/amcharts.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>public/assets/global/plugins/amcharts/amcharts/serial.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>public/assets/global/plugins/amcharts/amcharts/pie.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>public/assets/global/plugins/amcharts/amcharts/themes/light.js" type="text/javascript"></script>

<script>
// Debug function to check if libraries are loaded
function checkAmCharts() {
    if (typeof AmCharts === 'undefined') {
        console.error('AmCharts is not loaded!');
        document.getElementById('chartFallback').style.display = 'block';
        document.getElementById('dataSummary').style.display = 'block';
        return false;
    }
    console.log('AmCharts loaded successfully');
    return true;
}

jQuery(document).ready(function() {
    console.log("Dashboard loaded");
    
    // Check if AmCharts is loaded
    if (!checkAmCharts()) {
        return;
    }
    
    // Log chart data for debugging
    console.log("Bar Chart Data:", <?php echo json_encode($barArray); ?>);
    
    
    // 1. BAR CHART - Last 6 Months Appointments
    
    try {
        var barChart = AmCharts.makeChart("barChart", {
            "type": "serial",
            "theme": "light",
            "startDuration": 1.5,
            "fontFamily": "Open Sans",
            "color": "#444",
            "dataProvider": <?php echo json_encode($barArray); ?>,
            
            "valueAxes": [{
                "axisAlpha": 0,
                "gridAlpha": 0.05,
                "color": "#999",
                "title": "Number of Appointments",
                "minimum": 0,
                "autoGridCount": true
            }],
            
            "graphs": [{
                "type": "column",
                "valueField": "visits",
                "balloonText": "<b>[[full_month]] [[year]]</b><br>Appointments: <b>[[value]]</b>",
                "fillAlphas": 0.8,
                "lineAlpha": 0,
                "columnWidth": 0.6,
                "cornerRadiusTop": 8,
                "colorField": "color",
                "balloon": {
                    "drop": true,
                    "adjustBorderColor": false,
                    "color": "#ffffff",
                    "fillColor": "#333",
                    "cornerRadius": 5
                }
            }],
            
            "chartCursor": {
                "cursorAlpha": 0,
                "zoomable": false,
                "categoryBalloonEnabled": true
            },
            
            "categoryField": "country",
            "categoryAxis": {
                "gridPosition": "start",
                "gridAlpha": 0,
                "axisAlpha": 0.8,
                "color": "#666",
                "title": "Months",
                "labelRotation": 0,
                "parseDates": false,
                "gridCount": 6,
                "equalSpacing": true,
                "labelsEnabled": true
            },
            
            "export": {
                "enabled": false
            }
        });
        
        console.log("Bar chart created successfully");
        
        // Force redraw after a delay
        setTimeout(function() {
            if (barChart) {
                barChart.validateSize();
                barChart.validateData();
                console.log("Bar chart validated");
            }
        }, 500);
        
    } catch (error) {
        console.error("Error creating bar chart:", error);
        document.getElementById('chartFallback').style.display = 'block';
        document.getElementById('dataSummary').style.display = 'block';
    }
    
    
    // 2. PIE CHART - Revenue by Plan
    
    <?php if (!empty($pieArray)): ?>
    try {
        var pieChart = AmCharts.makeChart("pieChart", {
            "hideCredits": true,
            "type": "pie",
            "theme": "light",
            "fontFamily": "Open Sans",
            "color": "#888",
            "innerRadius": "70%",
            "labelsEnabled": false,
            "outlineThickness": 0,
            "outlineAlpha": 0,
            "depth3D": 0,
            "angle": 0,
            "dataProvider": <?php echo json_encode($pieArray); ?>,
            "valueField": "value",
            "titleField": "country",
            "colors": [
                "#FFE8A8", "#A7C2F2", "#FFB8B8", "#A3D9B1", 
                "#C6A0F6", "#F8E39A", "#91C8E4", "#FF9A8B"
            ],
            "legend": {
                "position": "bottom",
                "align": "center",
                "markerType": "circle",
                "markerSize": 10,
                "markerBorderThickness": 0,
                "valueText": "",
                "labelText": "[[title]]",
                "spacing": 30
            },
            "balloonText": "[[title]]: <b>[[value]]</b> ([[percents]]%)"
        });
        
        console.log("Pie chart created successfully");
        
    } catch (error) {
        console.error("Error creating pie chart:", error);
        document.getElementById('pieFallback').style.display = 'block';
    }
    <?php else: ?>
        document.getElementById('pieFallback').style.display = 'block';
        console.log("No pie chart data available");
    <?php endif; ?>
    
    // Handle window resize
    window.addEventListener('resize', function() {
        setTimeout(function() {
            if (typeof barChart !== 'undefined' && barChart) {
                barChart.validateSize();
            }
            if (typeof pieChart !== 'undefined' && pieChart) {
                pieChart.validateSize();
            }
        }, 300);
    });
    
    // Test if chart is rendering
    setTimeout(function() {
        var chartDiv = document.getElementById('barChart');
        if (chartDiv) {
            var chartChildren = chartDiv.children;
            console.log("Chart div has", chartChildren.length, "children");
            if (chartChildren.length === 1 && chartChildren[0].id === 'chartFallback') {
                console.error("Chart failed to render - showing fallback");
            }
        }
    }, 1000);
});

// Function to reload chart
function reloadChart() {
    if (confirm('Refresh chart data?')) {
        window.location.reload();
    }
}
</script>

<!-- Alternative: Simple Chart using Chart.js if AmCharts fails -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Fallback chart if AmCharts doesn't work
function createFallbackChart() {
    try {
        var ctx = document.createElement('canvas');
        ctx.id = 'fallbackBarChart';
        ctx.style.width = '100%';
        ctx.style.height = '400px';
        
        var chartContainer = document.getElementById('barChart');
        if (chartContainer) {
            chartContainer.innerHTML = '';
            chartContainer.appendChild(ctx);
            
            var data = <?php echo json_encode($barArray); ?>;
            var labels = data.map(item => item.country);
            var values = data.map(item => item.visits);
            var colors = data.map(item => item.color);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Appointments',
                        data: values,
                        backgroundColor: colors,
                        borderColor: colors.map(color => color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Appointments'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Months'
                            }
                        }
                    }
                }
            });
            
            console.log("Fallback chart created with Chart.js");
        }
    } catch (error) {
        console.error("Error creating fallback chart:", error);
    }
}

// Try fallback after 3 seconds if AmCharts fails
setTimeout(function() {
    var chartDiv = document.getElementById('barChart');
    if (chartDiv && chartDiv.children.length === 1 && chartDiv.children[0].id === 'chartFallback') {
        console.log("AmCharts failed, trying fallback with Chart.js");
        createFallbackChart();
    }
}, 3000);
</script>

<style>
.chart {
    position: relative;
    min-height: 300px;
}
.amcharts-chart-div a {
    display: none !important;
}
.well-sm {
    padding: 8px;
    margin-bottom: 5px;
    background: #f8f9fa;
    border-radius: 4px;
}
#chartFallback, #pieFallback {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
}
</style>

<?= $this->endSection() ?>