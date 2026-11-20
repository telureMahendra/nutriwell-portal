<?php
namespace App\Controllers\Admin;
use Config\Services;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Appointment extends Controller {
    protected $session;
    protected $validation;
    protected $common_model;
    protected $appointment_model;

    public function __construct() {
        $this->validation = Services::validation();
        $this->common_model = model('App\Models\Common_model', false);
        $this->appointment_model = model('App\Models\Appointment_model', false);
        $this->session = session();
        
        $controller = class_basename(service('router')->controllerName());
        $methodName = class_basename(service('router')->methodName());
    }

public function index()
{
    if ($this->session->has('userid')) {
        // Initialize variables
        $fromdate = '';
        $endDate = '';
        $search = '';
        
        // Check if filter is submitted
        if ($this->request->getGet('submit') == 'Filter') {
            $fromdate = $this->request->getGet('fromdate') ?? '';
            $endDate = $this->request->getGet('todate') ?? '';
            $search = $this->request->getGet('search') ?? '';
        }
        
        $data = [
            'fromdate' => $fromdate,
            'todate' => $endDate,
            'search' => $search,
            'menutitle' => 'Appointment',
            'pagetitle' => 'Appointment',
            'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage Appointment</li></ul>'
        ];
        
        // Build conditions - ONLY when dates are provided
        $condition = [];
        
        // Add date conditions ONLY if BOTH dates are provided
        if (!empty($fromdate) && !empty($endDate)) {
            $condition['A.date >='] = $fromdate;
            $condition['A.date <='] = $endDate;
        } 
        // Optional: If you want to handle single date filters
        elseif (!empty($fromdate)) {
            $condition['A.date'] = $fromdate; // Search for specific date only
        }
        // If no dates provided, show all dates (no date filter applied)
        
        // Always exclude cancelled appointments
        //$condition['A.status !='] = 'Cancelled';
        
        // Build search conditions
        $like = [];
        if (!empty($search)) {
            $like = [
                'CP.client_name' => $search,
                'CP.client_mobile_number' => $search,
                'D.doctor_name' => $search,
                'P.name' => $search,
                'A.slot' => $search,
            ];
        }
        
        // Get appointments
        $contest_images = $this->appointment_model->getAllAppointmentsForAdmin($condition, $like);
        
        // Download functionality
        if($this->request->getGet('download')!=""){                
            $this->download($contest_images);
            die();
        }  

        // Setup pagination
        $pager = service('pager');
        $page = (int)(($this->request->getVar('page')!==null) ? $this->request->getVar('page') : 1) - 1;
        $perPage = defined('PAGELIMIT') ? PAGELIMIT : 10;
        $total = count($contest_images);
        $pager->makeLinks($page+1, $perPage, $total);
        $offset = $page * $perPage;

        // Get paginated appointments
        $appointmentArray = $this->appointment_model->getAllAppointmentsForAdmin($condition, $like, $perPage, $offset);
        $data['appointmentList'] = $appointmentArray;
        $data['total'] = $total;
        
        echo view('admin/appointment/appointment_list', $data);
    } else {
        return view('admin/index');
    }
}
    
    public function close()
    {
        $appointment_id = $this->request->getPost('appointment_id');
        $condition = ['appointment_id' => $appointment_id];
        
        if ($appointment_id > 0) {
            $update_data = [
                'status' => 'Cancelled',
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];
            
            $id = $this->common_model->updateData('appointment', $update_data, $condition);
            echo $id;
        } else {
            echo 0;
        }
    }
    
    public function addplan($appointment_id)
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Appointment',
                'pagetitle' => 'Add Plan',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="'. base_url().'appointment">Manage Appointment</a><i class="fa fa-angle-right"></i></li><li>Add Plan</li></ul>'
            ];
            $data['appointment_id'] = $appointment_id;

            // FIXED: Use getAppointment method instead of getDoctorAppointment
            $appointmentArray = $this->appointment_model->getAppointment($appointment_id);
            $data['appointmentList'] = $appointmentArray;

            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }

            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/appointment/add_plan', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Add Plan') {
                $workoutplan = $this->request->getFile('workoutplan');
                $dietplan = $this->request->getFile('dietplan');
                
                if(!$workoutplan->isValid() && !$dietplan->isValid()) {
                    return view('admin/appointment/add_plan', $data);
                } else {
                    $workoutplan_srt = explode('.', $workoutplan->getName('workoutplan'));
                    $workoutplan_name = uniqid() . "." . $workoutplan_srt[1];
                    $workoutplan_targetpath = "./public/uploads/appointment/plan/workoutplan";
                    $workoutplan->move($workoutplan_targetpath, $workoutplan_name);

                    $dietplan_srt = explode('.', $dietplan->getName('dietplan'));
                    $dietplan_name = uniqid() . "." . $dietplan_srt[1];
                    $header_targetpath = "./public/uploads/appointment/plan/dietplan";
                    $dietplan->move($header_targetpath, $dietplan_name);

                    $update_data = [
                        'workoutplan' => $workoutplan_name,
                        'dietplan' => $dietplan_name,
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];
                    $condition = ['appointment_id' => $appointment_id];
                    
                    $id = $this->common_model->updateData('appointment', $update_data, $condition);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg'=>'Plans added successfully!!!','msg-type'=>'success');
                    } else {
                        $arr_msg = array('suc_msg'=>'Failed to add Plans','msg-type'=>'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('appointment');
                }
            }
        } else {
            return view('admin/index');
        }
    }

    public function download_diet($dietplan)
    {
        if ($dietplan != '') {
            $file = $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/healthie4u/public/uploads/appointment/plan/dietplan/'.$dietplan;
            return $this->response->download($file, null);
        }
    }
    
    public function download_workout($workoutplan)
    {
        if ($workoutplan != '') {
            $file = $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/healthie4u/public/uploads/appointment/plan/workoutplan/'.$workoutplan;
            return $this->response->download($file, null);
        }
    }

    public function client_info()
    {
        $client_id = $this->request->getPost('client_id');
        $result = [];
        if ($client_id > 0) {
            // Check if this method exists in your model
            $clientData = $this->appointment_model->getClientInfo($client_id);
            
            if (!empty($clientData)) {
                $result['clientData'] = $clientData;
                $result['outcome'] = 1;
            } else {
                $result['outcome'] = 0;
            }
        } else {
            $result['outcome'] = 0;
        }
        echo json_encode($result); die();
    }

    public function download($result)
    {    
        require(APPPATH.'ThirdParty/phpspreadsheet/vendor/autoload.php');   
        $fileName = 'AppointmentData-'.time().'.xlsx'; 
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Slot');
        $sheet->setCellValue('C1', 'Client');
        $sheet->setCellValue('D1', 'Plan');
        $sheet->setCellValue('E1', 'Mentor');
        $sheet->setCellValue('F1', 'Designation');
        $sheet->setCellValue('G1', 'Experience');
        $sheet->setCellValue('H1', 'Status');
        
        $rowCount = 2;
        foreach ($result as $row) {
            $sheet->setCellValue('A' . $rowCount, $row->date);
            $sheet->setCellValue('B' . $rowCount, $row->slot);
            $sheet->setCellValue('C' . $rowCount, $row->client_name);
            $sheet->setCellValue('D' . $rowCount, $row->name);
            $sheet->setCellValue('E' . $rowCount, $row->doctor_name);
            $sheet->setCellValue('F' . $rowCount, $row->designation);
            $sheet->setCellValue('G' . $rowCount, $row->years.' year & '.$row->month.' months');
            $sheet->setCellValue('H' . $rowCount, $row->status);
            $rowCount++;
        }
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0'); 
        $writer->save('php://output');
        exit;
    }    

    public function listcomment($id)
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Comment',
                'pagetitle' => 'Comment',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"> </i> </li> <a href="' . base_url('appointment') . '">Appointment</a> <i class="fa fa-angle-right"> </i> </li>  <li>Manage Comment</li></ul>',
                "appointment_id" => $id
            ];
            
            $appointmentcomment_Array = $this->appointment_model->getAppointmentComment($id);
            $data['appointmentcomment'] = $appointmentcomment_Array;

            echo view('admin/appointment/comment', $data);
        } else {
            return view('admin/index');
        }
    }
    
    public function postcomment()
    {
        $comment_id = trim($this->request->getPost('comment_id'));
        $appointment_id = trim($this->request->getPost('appointment_id'));
        $comment = trim($this->request->getPost('comment'));
        
        if ($comment_id > 0) {
            $condition = ['comment_id' => $comment_id];
            $update_data['comment'] = $comment;
            $id = $this->common_model->updateData('appointmentcomment', $update_data, $condition);
            if ($id > 0) {
                echo 1;
            } else {
                echo 0;
            }
        } else {
            $insert_data['appointment_id'] = $appointment_id;
            $insert_data['date'] = date('Y-m-d H:i:s');
            $insert_data['comment'] = $comment;
            $insert_data['user_id'] = session()->get('userid');
            $id = $this->common_model->addData('appointmentcomment', $insert_data);
            if ($id > 0) {
                echo 1;
            } else {
                echo 0;
            }
        }
    }
    
    public function closecomment()
    {
        $comment_id = $this->request->getPost('comment_id');
        $condition = ['comment_id' => $comment_id];
        if ($comment_id > 0) {
            $id = $this->common_model->deleteData('appointmentcomment', $condition);
            echo $id;
        } else {
            echo 0;
        }
    }
    
    public function clientdashboard($id)
    {
        if ($this->session->has('userid')) {
            $db = \Config\Database::connect();
            $clientRow = $db->query("SELECT * FROM client_profile WHERE client_id = " . (int)$id)->getRowArray();
            
            $data = [
                'menutitle' => 'Client Dashboard',
                'pagetitle' => 'Client Dashboard',
                "client_id" => $id,
                'clientDetails' => [
                    (object)[
                        'client_id' => $id,
                        'client_name' => $clientRow ? $clientRow['client_name'] : 'Unknown'
                    ]
                ]
            ];
        
            echo view('admin/appointment/clientdashboard', $data);
        } else {
            return view('admin/index');
        }
    }

    public function set_client_targets($client_id)
    {
        if (!$this->session->has('userid')) {
            return redirect()->to('');
        }

        $db = \Config\Database::connect();
        $db->table('client_profile')->where('client_id', $client_id)->update([
            'target_water'  => (int)$this->request->getPost('target_water'),
            'target_sleep'  => (int)$this->request->getPost('target_sleep'),
            'target_weight' => (float)$this->request->getPost('target_weight'),
        ]);

        model('App\Models\StepsTargetModel')->setTarget($client_id, (int)$this->request->getPost('target_steps'));
        model('App\Models\CaloriesTargetModel')->setTarget($client_id, (int)$this->request->getPost('target_calorie'), 'intake');

        return redirect()->to('appointment/clientdashboard/' . $client_id . '?targets_saved=1');
    }
}