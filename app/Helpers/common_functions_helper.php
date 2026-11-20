<?php
define("PAGELIMIT", 100);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Dompdf\Dompdf;


if (!function_exists('is_admin_logged_in')) {
	function is_admin_logged_in()
	{
		$user = session()->get('userid');
		$userrole = session()->get('userrole');
		$isLogin = session()->get('is_login');

		if (($user > 0) && (($userrole == 'Admin') || ($userrole == 'User') || ($userrole == 'SalesAgent'))) {
			return true;
		} else {
			return false;
		}
	}
}

if (!function_exists('is_doctor_logged_in')) {
	function is_doctor_logged_in()
	{

		$user = session()->get('userid');
		$userrole = session()->get('userrole');
		$isLogin = session()->get('is_login');

		if (($user > 0) && ($userrole == 'Doctor') || ($userrole == 'Dietitian') || ($userrole == 'Psychologist') || ($userrole == 'Fitness Trainer')) {
			return true;
		} else {
			return false;
		}
	}
}

if (!function_exists('CheckAccess')) {
	function CheckAccess($controllerName, $methodName)
	{
		$roleArray = session()->get('roleArray');
		if (array_key_exists($controllerName, $roleArray)) {
			$roleData = $roleArray[$controllerName];
			if (in_array($methodName, $roleData)) {
				return true;
			}
		}
		return false;
	}
}
if (!function_exists('hasAccess')) {
	function hasAccess($controller, $methodName)
	{
		$username = session()->get('username');
		$userrole = session()->get('userrole');
		if ($username == "" && $userrole == "") {
			header('location:' . base_url());
			exit(0);
		} else {
			if ($methodName != "add_new_row") {
				if (!CheckAccess($controller, $methodName)) {
					header('location:' . base_url('home'));
					exit(0);
				}
			}
		}
		//return null;
	}
}


if (!function_exists('sendNotification')) {
	function sendNotification($client_id, $title, $message, $registration_ids, $image = "")
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>
				//"to"=>"eVpFGhJXTQC2OrJbQ1fOQd:APA91bHMia6ybrpFDwX4EFFka-aOCVGZYDX_6Rcrey3NvRzt7spgep2sy3sgh1DC1KqjxOHoVEkDF3CxjGvpyBBD3Fx7Gp-0ikeAv8aJKqK33TnnfgNIqxnLAYuq7bm-OxdFEwf8rubD",
				json_encode(
					array(
						"registration_ids" => $registration_ids,
						"notification" => array("title" => $title, "body" => $message, "image" => $image),
						"data" => array(
							"notification_type" => "14",
							"title" => $title,
							"body" => $message,
							"image" => $image
						)
					)
				),
			CURLOPT_HTTPHEADER => array(
				'Authorization:key=AAAAe7EGZ0U:APA91bHIpeTm6u0DwoydTRydjrqcFvzhPkni_WirBPsZKgDNbM4EUkDAtLrJ7FTPtvFftXo_jilHtXLUrx1T7mTbsLMQQVQPDL3DxBMOMaWtJvCFn0v5JkgLa87X0WnoJVYTN5DVFtHA',
				'Content-Type:application/json'
			)
		));

		curl_setopt($curl, CURLOPT_TIMEOUT, 1);
		curl_setopt($curl, CURLOPT_NOSIGNAL, 1);

		// Set For Localhost Only
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

		$response = curl_exec($curl);

		curl_close($curl);
		//return $response;
	}
}
if (!function_exists('sendSms')) {
	function sendSms($mobileNo, $sms)
	{
		$apiKey = urlencode('NzA3ODY3Nzg2MzU1Nzg3NjYxNDg2YjUyMzE2YjM1NmE=');
		$sender = urlencode('KGSHLT');
		$message = urlencode($sms);
		$data = array('apikey' => $apiKey, 'numbers' => $mobileNo, "sender" => $sender, "message" => $message);

		$ch = curl_init('https://api.textlocal.in/send/');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1);

		// Set For Localhost Only
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		$response = curl_exec($ch);
		curl_close($ch);
		return true;
	}
}




if (!function_exists('sendOtpVia2Factor')) {

	function sendOtpVia2Factor($mobileNo, $otp)
	{
		$apiKey = '9731e9ae-d75a-11f0-a6b2-0200cd936042'; // move to .env ideally
		$template = 'LoginOTP';

		// Ensure international format
		if (substr($mobileNo, 0, 1) !== '+') {
			$mobileNo = '+91' . $mobileNo;
		}

		$url = "https://2factor.in/API/V1/{$apiKey}/SMS/{$mobileNo}/{$otp}/{$template}";

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
		]);

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}
}

// CCAVENUES

/* Test Server */
// define("_merchant_id", "2730685");
// define("_working_key", "04602A608FF2C78CB52F8E3A54A14046");
// define("_access_code", "AVNC96KH16AK95CNKA");


/* Live Server */
define("_merchant_id", "3728853");
define("_working_key", "4AE82FE6FC9322688D3D2C31A049FF0C");
define("_access_code", "AVWR08LH50AB76RWBA");


define("_redirect_url", base_url('payment/success'));
define("_cancel_url", base_url('payment/cancel'));
define("_language", "EN");
define("_currency", "INR");

function encrypt($plainText, $key)
{
	$key = hextobin(md5($key));
	$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
	$openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
	$encryptedText = bin2hex($openMode);
	return $encryptedText;
}

function decrypt($encryptedText, $key)
{
	$key = hextobin(md5($key));
	$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
	$encryptedText = hextobin($encryptedText);
	$decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
	return $decryptedText;
}
//*********** Padding Function *********************
function pkcs5_pad($plainText, $blockSize)
{
	$pad = $blockSize - (strlen($plainText) % $blockSize);
	return $plainText . str_repeat(chr($pad), $pad);
}
//********** Hexadecimal to Binary function for php 4.0 version ********
function hextobin($hexString)
{
	$length = strlen($hexString);
	$binString = "";
	$count = 0;
	while ($count < $length) {
		$subString = substr($hexString, $count, 2);
		$packedString = pack("H*", $subString);
		if ($count == 0) {
			$binString = $packedString;
		} else {
			$binString .= $packedString;
		}

		$count += 2;
	}
	return $binString;
}



if (!function_exists('sendEmail')) {
	function sendEmail($booking_id)
	{
		try {
			$condition = ['booking_id' => $booking_id];
			$tableName  = 'booking AS B';

			$booking_model = new \App\Models\Booking_model();
			$bookingArray  = $booking_model->getBookingList($tableName, $condition, '');

			if (empty($bookingArray)) {
				return false;
			}

			$booking      = $bookingArray[0];
			$client_name  = $booking->client_name;
			$client_email = $booking->client_email;

			if (empty(trim($client_name)) || empty(trim($client_email))) {
				return false;
			}

			$body  = '<p>Dear ' . $client_name . ',</p>';
			$body .= '<p>Your payment has been successfully processed, and your invoice is now marked as paid.</p>';
			$body .= '<p>Please find your invoice attached.</p>';
			$body .= '<p>Thank you,<br>Healthie4u Team</p>';

			$data['booking'] = $bookingArray;
			$html = view('admin/booking/invoice', $data);

			require_once(APPPATH . 'ThirdParty/dompdf/vendor/autoload.php');
			$dompdf = new \Dompdf\Dompdf();
			$options = $dompdf->getOptions();
			$options->set(['isRemoteEnabled' => true]);
			$dompdf->setOptions($options);
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			$pdfOutput = $dompdf->output();

			$InvoiceNo = sprintf("%08d", $booking_id);
			$fileName  = "Invoice-" . $InvoiceNo . ".pdf";

			require_once(APPPATH . 'ThirdParty/phpmailer/vendor/autoload.php');
			$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
			$mail->SMTPDebug  = 0;
			$mail->isSMTP();
			$mail->Host       = 'host5.xhost.co.in';
			$mail->SMTPAuth   = true;
			$mail->Username   = 'support@healthie4u.com';
			$mail->Password   = 'support@healthie4u';
			$mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
			$mail->Port       = 587;
			$mail->setFrom('support@healthie4u.com', 'Healthie4u');
			$mail->addAddress($client_email, $client_name);
			$mail->addStringAttachment($pdfOutput, $fileName);
			$mail->isHTML(true);
			$mail->Subject = 'Payment Confirmation for Invoice #' . $InvoiceNo;
			$mail->Body    = $body;

			return $mail->send();

		} catch (\Exception $e) {
			log_message('error', 'sendEmail failed for booking #' . $booking_id . ': ' . $e->getMessage());
			return false;
		}
	}
}
