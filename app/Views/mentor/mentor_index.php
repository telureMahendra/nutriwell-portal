<?php 
$session = session(); 
$doctor_id = $session ->get("doctor_id");



$db = db_connect();
$sqlF= "
SELECT 
	booking.booking_id,booking.date,booking.total_amount,
	client_profile.client_name,
	plan.name as planName
FROM 
	booking 
INNER JOIN
	client_profile ON booking.client_id=client_profile.client_id
INNER JOIN
	plan ON booking.plan_id=plan.plan_id
WHERE 
	booking.status='Active' AND 
	booking.date='".date('Y-m-d')."' AND 
    booking.doctor_id='".$doctor_id."'
GROUP BY
	booking.booking_id
";
$queryF = $db->query($sqlF);
$resultF = $queryF->getResultArray();



$salesAmount=0;
$sqlB= "SELECT sum(total_amount) as salesAmount from booking WHERE doctor_id='".$doctor_id."' AND status='Active'";
$queryB = $db->query($sqlB);
$resultB = $queryB->getRowArray();
if ($resultB) {
	$salesAmount =$resultB["salesAmount"];
}

?>




<link href="<?= base_url(); ?>public/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css" />
<?= $this->extend("layouts/admin_default"); ?>
<?= $this->section("content"); ?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            Dashboard
        </li>
    </ul>
</div>
<h3 class="page-title">
    Dashboard
</h3>
<div class="row">

<div class="col-md-8">
					<div class="portlet box blue">
						<div class="portlet-title">
							<div class="caption">
                            <i class="fa fa-calendar"></i>Today’s bookings
							</div>
							
						</div>
						<div class="portlet-body">
							<div class="table-scrollable" style="height: 220px;overflow-y: scroll;">
								<table class="table table-bordered table-hover">
								<thead>
								<tr>
									<th>#</th>
                                    <th>Date</th>
									<th>Name</th>
									<th>Plan</th>
									<th>Amount</th>
								</tr>
								</thead>
								<?php if($resultF){$srNo=0;foreach($resultF as $row){$srNo++;?>
								<tr>
									<td><?php echo $srNo;?></td>
									<td><?php echo date('d/m/Y',strtotime($row["date"]));?></td>
									<td><?php echo $row["client_name"];?></td>
                                    <td><?php echo $row["planName"];?></td>
									<td><?php echo number_format($row["total_amount"],2);?></td>
								</tr>
								<?php }}?>			               
								</table>
							</div>
						</div>
					</div>
				</div>   




                <div class="col-md-4">
        <div class="dashboard-stat blue-madison">
            <div class="visual">
                <i class="icon-users"></i>
            </div>
            <div class="details">
                <div class="number">
                <i class="fa fa-inr"></i> <?php echo $salesAmount; ?>

                </div>
                <div class="desc">
                    Total Sales Amount
                </div>
            </div>
            <!-- <a class="more" href="<?php //echo base_url(); ?>mentor/client">
                View more <i class="m-icon-swapright m-icon-white"></i>
            </a> -->
        </div>
    </div>

</div>
<?= $this->endSection() ?>