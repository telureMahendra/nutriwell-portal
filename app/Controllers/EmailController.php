<?php

namespace App\Controllers;

use Config\Services;
use CodeIgniter\Controller;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Dompdf\Dompdf;


class EmailController extends Controller
{

	public function __construct()
	{
		$this->common_model = model('App\Models\Common_model', false);
		$this->booking_model = model('App\Models\Booking_model', false);
	}
	public function index($booking_id)
	{
		try {

			$html = '';
			$condition = ['booking_id' => $booking_id];
			$tableName = 'booking AS B';
			$bookingArray = $this->booking_model->getBookingList($tableName, $condition, '');

			if ($bookingArray) {
				$booking =  $bookingArray[0];
				$client_name =  $booking->client_name;
				$client_email = $booking->client_email;

				if ($client_name != "" && $client_email != "") {

					$body = '<p>Dear ' . $client_name . '</p>';
					$body .= '<p>Your payment has been successfully processed, and your invoice is now marked as paid.</p>';

					$data['booking'] = $bookingArray;
					$html = view('admin/booking/invoice', $data);

					require(APPPATH . 'ThirdParty/dompdf/vendor/autoload.php');
					$dompdf = new Dompdf();
					$options = $dompdf->getOptions();
					$options->set(array('isRemoteEnabled' => true));
					$dompdf->setOptions($options);
					$dompdf->loadHtml($html);
					$dompdf->setPaper('A4', 'portrait');
					$dompdf->render();

					$InvoiceNo = sprintf("%08d", $booking_id);
					$fileName = "Invoice-" . $InvoiceNo . ".pdf";
					$fullPath = FCPATH . "\\public\\uploads\\" . $fileName;
					$pdfOutput = $dompdf->output();

					require(APPPATH . 'ThirdParty/phpmailer/vendor/autoload.php');
					$mail = new PHPMailer(true);
					$mail->SMTPDebug = 0;
					$mail->isSMTP();
					$mail->Host       = 'mail.drrajeevpathakent.in';
					$mail->SMTPAuth   = true;
					$mail->Username   = 'test@drrajeevpathakent.in';
					$mail->Password   = 'Info@2024';
					$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
					$mail->Port       = 587;
					$mail->setFrom('test@drrajeevpathakent.in', 'drrajeevpathakent');
					$mail->addAddress('kiran.hujare@gmail.com', 'kiran hujare');
					$mail->addStringAttachment($pdfOutput, $fileName);
					$mail->isHTML(true);
					$mail->Subject = 'Payment Confirmation for Invoice #' . $InvoiceNo;
					$mail->Body    = $body;
					return $mail->send();
				}
			}
		} catch (Exception $e) {
			return $mail->ErrorInfo;
		}
	}
}
