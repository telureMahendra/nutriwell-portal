<?php

namespace App\Controllers;

use Config\Services;
use CodeIgniter\Controller;

class Notifications extends Controller
{

	public function __construct()
	{
	}
	public function index()
	{
		return $this->response->setJSON(array('status' => false, 'msg' => 'Invalid Action'));
		die();
	}

	public function planexpire()
	{

		$db = \Config\Database::connect();

		// Plan Expire in next 2 days
		$nTitle = "Plan Expiring";
		$nMessage = "Your current plan is expiring in 2 days. Kindly review and take necessary action.";


		$todayDate = date('Y-m-d');
		$planDate = date('Y-m-d', strtotime($todayDate . ' + 2 days'));
		$query   = $db->query("
		SELECT 
			booking.booking_id,booking.booking_id,booking.plan_expiry_date,
			plan.name,
			client_login.*			
		FROM 
			booking 
		INNER JOIN 
			plan ON booking.plan_id=plan.plan_id 
		INNER JOIN 
			client_login ON booking.client_id=client_login.client_id 			
		WHERE 
			booking.status IN('Active') 
			AND client_login.client_status='Active'			
		");
		//AND client_login.fcm_token!=''
		//AND DATE(booking.plan_expiry_date)='".$planDate."'

		$results = $query->getResultArray();
		$fcmArray = [];
		$notificationArray = [];
		foreach ($results as $row) {
			$fcmArray[] = $row["client_phone"];
			$notificationArray = array("client_id"=>$row["client_id"],"notification_title"=>$nTitle,"description"=>$nMessage,"type_id"=>1,"created_on"=>date('Y-m-d H:i:s'));
		}

		if (count($fcmArray) > 0) {
			print '<pre>';
			print_r($fcmArray);
			//sendNotification($nTitle, $nMessage, $fcmArray);
		}
		if (count($notificationArray) > 0) {
			$builder = $db->table("notification");
			$builder->insertBatch($notificationArray); 
		}

	}
	public function planexpired()
	{
		$db = \Config\Database::connect();

		// Plan Expire in next 2 days
		$planDate = date('Y-m-d');
		$nTitle = "Plan Expired";
		$nMessage = "Your current plan has expired. Please review and take necessary action promptly.";

		$query   = $db->query("
		SELECT 
			booking.booking_id,booking.booking_id,booking.plan_expiry_date,
			plan.name,
			client_login.*			
		FROM 
			booking 
		INNER JOIN 
			plan ON booking.plan_id=plan.plan_id 
		INNER JOIN 
			client_login ON booking.client_id=client_login.client_id 			
		WHERE 
			booking.status IN('Active') 
			AND client_login.client_status='Active'			
		");
		//AND client_login.fcm_token!=''
		//AND DATE(booking.plan_expiry_date)='".$planDate."'

		$results = $query->getResultArray();
		$fcmArray = [];
		$notificationArray = [];
		foreach ($results as $row) {
			$fcmArray[] = $row["client_phone"];
			$notificationArray = array("client_id"=>$row["client_id"],"notification_title"=>$nTitle,"description"=>$nMessage,"type_id"=>2);
		}

		if (count($fcmArray) > 0) {
			print '<pre>';
			print_r($fcmArray);
			//sendNotification($nTitle, $nMessage, $fcmArray);
		}
		if (count($notificationArray) > 0) {
			$builder = $db->table("notification");
			$builder->insertBatch($notificationArray); 
		}		
	}
	public function tomorrowappointment()
	{
		$db = \Config\Database::connect();

		// Appointment Expire in next 1 days
		$todayDate = date('Y-m-d');
		$planDate = date('Y-m-d', strtotime($todayDate . ' -1 days'));
		$nTitle = "Upcoming appointment";
		$nMessage = "A friendly reminder that your upcoming appointment is scheduled for tomorrow. We look forward to seeing you then.";


		$query   = $db->query("
		SELECT 
			appointment.appointment_id,appointment.date,
			client_login.*			
		FROM 
			appointment 
		INNER JOIN 
			client_login ON appointment.client_id=client_login.client_id 			
		WHERE 
			appointment.status IN('Active','Upcoming') 
			AND client_login.client_status='Active'			
		");
		//AND client_login.fcm_token!=''
		//AND DATE(appointment.date)='".$planDate."'

		$results = $query->getResultArray();
		$fcmArray = [];
		$notificationArray = [];
		foreach ($results as $row) {
			$fcmArray[] = $row["client_phone"];
			$notificationArray = array("client_id"=>$row["client_id"],"notification_title"=>$nTitle,"description"=>$nMessage,"type_id"=>3);
		}

		if (count($fcmArray) > 0) {
			print '<pre>';
			print_r($fcmArray);
			//sendNotification($nTitle, $nMessage, $fcmArray);
		}
		if (count($notificationArray) > 0) {
			$builder = $db->table("notification");
			$builder->insertBatch($notificationArray); 
		}
	}

}
