<?php

namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;
use CodeIgniter\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;



class Food extends Controller
{
    protected $session;
    protected $validation;
    protected $common_model;
    protected $food_model;

    public function __construct()
    {
        // Load the validation library

        $this->validation = Services::validation();
        $this->common_model = model('App\Models\Common_model', false);
        $this->food_model = model('App\Models\FoodModel', false);

        $this->session = session();
        if (!is_admin_logged_in()) {
            redirect('');
        }
        $controller = class_basename(service('router')->controllerName());
        $methodName  = class_basename(service('router')->methodName());
        //hasAccess($controller, $methodName);
    }

    public function index()
{
    if ($this->session->has('userid')) {
        $data = [
            'menutitle' => 'Food',
            'pagetitle' => 'Food',
            'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"> </i> </li> <li>Manage Food</li></ul>'
        ];
        $condition = ['status' => 'Active'];
        
        // Get items per page from URL or use default
        $perPage = $this->request->getGet('perPage') ? (int)$this->request->getGet('perPage') : 10;
        
        // Get sorting parameters
        $sort = $this->request->getGet('sort') ? $this->request->getGet('sort') : 'food_name';
        $order = $this->request->getGet('order') ? $this->request->getGet('order') : 'asc';
        
        // Get total count - using getAllData first (simpler approach)
        $food_Array_all = $this->common_model->getAllData('food', $condition);
        $total = count($food_Array_all);
        
        // Initialize pager
        $pager = \Config\Services::pager();
        $page = (int)($this->request->getVar('page') ?? 1);
        
        // Create pager links - use 'group' parameter
        $pager->makeLinks($page, $perPage, $total, 'default_full');
        
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // Get paginated data with sorting
        $food_Array = $this->food_model->getFoodList('food', $condition, $perPage, $offset, $sort, $order);
        
        // Pass data to view
        $data['food'] = $food_Array;
        $data['pager'] = $pager;
        $data['total'] = $total;
        $data['currentPage'] = $page;
        $data['perPage'] = $perPage;
        $data['sort'] = $sort;
        $data['order'] = $order;
        
        echo view('admin/food/index', $data);
    } else {
        return view('admin/index');
    }
}
    public function add()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Food',
                'pagetitle' => 'Add Food',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'food">Manage Food</a><i class="fa fa-angle-right"></i></li><li>Add Food</li></ul>'
            ];


            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }
            if (trim($this->request->getPost('submit')) == '') {


                $food_type_option = '';
                $condition = ['status' => 'Active'];
                $food_Array =  (array) $this->common_model->getAllData('food_type', $condition);
                if ($food_Array) {
                    foreach ($food_Array as $food) {
                        $food_type_option .= '<option value="' . $food->food_type_id . '">' . $food->food_type_name . '</option>';
                    }
                }
                $data['food_type_option'] = $food_type_option;


                return view('admin/food/add_food', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Add Food') {

                $validationRules = [
                    'food_name' => ['label' => 'Food', 'rules' => 'required'],
                    'calories' => ['label' => 'Calories', 'rules' => 'required']
                ];
                if (!$this->validate($validationRules)) {
                    return view('admin/food/add_food', $data);
                } else {
                    $food_type_id = '';
                    if ($this->request->getPost('food_type_id') != "") {
                        $food_type_id = implode(',', $this->request->getPost('food_type_id'));
                    }

                    $insert_data = [
                        'food_name' => trim($this->request->getPost('food_name')),
                        'calories' => trim($this->request->getPost('calories')),
                        'unit' => normalizeFoodUnit($this->request->getPost('unit')),
                        'food_type_id' => $food_type_id,
                        'status' => 'Active',
                        'created_on' => date('Y-m-d'),
                        'created_by' => session()->get('userid'),
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];
                    // echo "<pre>";print_r($insert_data);die();
                    $id = $this->common_model->addData('food', $insert_data);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg' => 'Food added successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to add Food', 'msg-type' => 'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('food');
                }
            }
        } else {
            return view('admin/index');
        }
    }


    public function edit($food_id)
    {
        if ($food_id > 0) {
            $data = [
                'menutitle' => 'Food',
                'pagetitle' => 'Edit Food',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'food">Manage Food</a><i class="fa fa-angle-right"></i></li> <li>Edit Food </li></ul>'
            ];
            $condition = ['food_id' => $food_id];
            $foodTypeDetails = $this->common_model->getAllData('food', $condition);
            $data['foodTypeDetails'] = $foodTypeDetails;
            //echo "<pre>";print_r($data);die();



            $food_type_array  = [];
            if ($foodTypeDetails[0]->food_type_id  > 0) {
                $food_type_array = explode(',', $foodTypeDetails[0]->food_type_id);
            }


            $food_type_option = '';
            $condition = ['status' => 'Active'];
            $food_Array =  (array) $this->common_model->getAllData('food_type', $condition);
            if ($food_Array) {
                foreach ($food_Array as $food) {
                    $sel = '';
                    if (in_array($food->food_type_id, $food_type_array)) {
                        $sel = 'selected="selected"';
                    }
                    $food_type_option .= '<option value="' . $food->food_type_id . '" ' . $sel . '>' . $food->food_type_name . '</option>';
                }
            }
            $data['food_type_option'] = $food_type_option;


            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/food/edit_food', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Edit Food') {

                $validationRules = [
                    'food_name' => ['label' => 'Food', 'rules' => 'required'],
                    'calories' => ['label' => 'Calories', 'rules' => 'required']
                ];

                if (!$this->validate($validationRules)) {
                    return view('admin/food/edit_food', $data);
                } else {
                    $food_type_id = '';
                    if (count($this->request->getPost('food_type_id')) > 0) {
                        $food_type_id = implode(',', $this->request->getPost('food_type_id'));
                    }

                    $update_data = [
                        'food_name' => trim($this->request->getPost('food_name')),
                        'calories' => trim($this->request->getPost('calories')),
                        // null clears the stored unit, putting the row back on the
                        // derived-from-name fallback.
                        'unit' => normalizeFoodUnit($this->request->getPost('unit')),
                        'food_type_id' => $food_type_id,
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];
                    $condition = ['food_id' => $food_id];

                    $id = $this->common_model->updateData('food', $update_data, $condition);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg' => 'Food update successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to update Food', 'msg-type' => 'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('food');
                }
            }
        } else {
            return view('admin/index');
        }
    }



    public function close()
    {
        $food_id = $this->request->getPost('food_id');
        if ($food_id > 0) {
            $update_data = [
                'status' => 'Inactive',
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];
            $condition = ['food_id' => $food_id];
            $id = $this->common_model->updateData('food', $update_data, $condition);
            echo $id;
        } else {
            echo 0;
        }
    }

    public function list_type()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Food Type',
                'pagetitle' => 'Food Type',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"> </i> </li> <li>Manage Food Type</li></ul>'
            ];
            $condition = ['status' => 'Active'];
            $food_type_Array = $this->common_model->getAllData('food_type', $condition);
            $data['food_type'] = $food_type_Array;
            // echo "<pre>";print_r($data);die();

            echo view('admin/food/list_type', $data);
        } else {
            return view('admin/index');
        }
    }
    public function add_type()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Food Type',
                'pagetitle' => 'Add Food Type',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'food/list_type">Manage Food Type</a><i class="fa fa-angle-right"></i></li><li>Add Food Type</li></ul>'
            ];
            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }
            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/food/add_type', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Add Food Type') {

                $validationRules = [
                    'food_type_name' => ['label' => 'Food Type', 'rules' => 'required'],
                ];
                if (!$this->validate($validationRules)) {
                    return view('admin/food/add_type', $data);
                } else {
                    $insert_data = [
                        'food_type_name' => trim($this->request->getPost('food_type_name')),
                        'status' => 'Active',
                        'created_on' => date('Y-m-d'),
                        'created_by' => session()->get('userid'),
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];
                    // echo "<pre>";print_r($insert_data);die();
                    $id = $this->common_model->addData('food_type', $insert_data);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg' => 'Food Type added successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to add Food Type', 'msg-type' => 'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('food/list_type');
                }
            }
        } else {
            return view('admin/index');
        }
    }
    public function edit_type($food_type_id)
    {
        if ($food_type_id > 0) {
            $data = [
                'menutitle' => 'Food Type',
                'pagetitle' => 'Edit Food Type',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'food/list_type">Manage Food Type</a><i class="fa fa-angle-right"></i></li> <li>Edit Food Type </li></ul>'
            ];
            $condition = ['food_type_id' => $food_type_id];
            $foodTypeDetails = $this->common_model->getAllData('food_type', $condition);
            $data['foodTypeDetails'] = $foodTypeDetails;
            //echo "<pre>";print_r($data);die();

            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/food/edit_type', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Edit Food Type') {

                $validationRules = [

                    'food_type_name' => ['label' => 'Food Type', 'rules' => 'required'],
                ];

                if (!$this->validate($validationRules)) {
                    return view('admin/food/edit_type', $data);
                } else {
                    $update_data = [
                        'food_type_name' => trim($this->request->getPost('food_type_name')),
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];
                    $condition = ['food_type_id' => $food_type_id];

                    $id = $this->common_model->updateData('food_type', $update_data, $condition);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg' => 'Food Type update successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to update Food Type', 'msg-type' => 'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('food/list_type');
                }
            }
        } else {
            return view('admin/index');
        }
    }
    public function close_type()
    {
        $food_type_id = $this->request->getPost('food_type_id');
        if ($food_type_id > 0) {
            $update_data = [
                'status' => 'Inactive',
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];
            $condition = ['food_type_id' => $food_type_id];
            $id = $this->common_model->updateData('food_type', $update_data, $condition);
            echo $id;
        } else {
            echo 0;
        }
    }

    public function importfood()
    {
        require(APPPATH . 'ThirdParty/phpspreadsheet/vendor/autoload.php');

        // The full recipe sheet is ~9,100 rows and takes about 25 seconds and
        // 70 MB to import, which overruns the usual 30s / 128M defaults and
        // surfaces as a 500. The front end already shows a "Processing..." modal
        // for the wait.
        set_time_limit(300);
        if ((int) ini_get('memory_limit') > 0 && (int) ini_get('memory_limit') < 512) {
            ini_set('memory_limit', '512M');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] === UPLOAD_ERR_OK) {
                try {
                    $tempFilePath = $_FILES['excelFile']['tmp_name'];

                    $spreadsheet = IOFactory::load($tempFilePath);
                    $sheet = $spreadsheet->getActiveSheet();

                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();

                    $insertCount = 0;
                    $skipCount = 0;
                    $errorCount = 0;
                    $lastError = '';
                    $unitRejects = [];

                    // Cache food types to prevent N+1 query issue (which likely causes 500 timeout for large files)
                    $food_types_cache = [];
                    $all_food_types = $this->common_model->getAllData('food_type', ['status' => 'Active']);
                    if ($all_food_types) {
                        foreach ($all_food_types as $type) {
                            $food_types_cache[strtolower($type->food_type_name)] = $type->food_type_id;
                        }
                    }

                    // Same reason: one query for the whole catalogue instead of a
                    // SELECT per row. A 9,000 row sheet was 9,000 queries.
                    $existing_food_cache = [];
                    $all_food = $this->common_model->getData('food', 'food_id, food_name', '', '', '', '', '', '');
                    if ($all_food) {
                        foreach ($all_food as $existing) {
                            $existing_food_cache[strtolower(trim($existing->food_name))] = $existing->food_id;
                        }
                    }

                    // Work out which column holds what from the header row, so a
                    // Unit column is read as a unit and not mistaken for a food type.
                    $columns = detectFoodSheetColumns($sheet, $highestColumn);

                    $updateCount = 0;

                    for ($row = 2; $row <= $highestRow; ++$row) {
                        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false)[0];

                        $cell = function ($index) use ($rowData) {
                            return ($index !== null && isset($rowData[$index])) ? trim((string)$rowData[$index]) : '';
                        };

                        $unit_raw = '';

                        if ($columns !== null) {
                            $food_name = $cell($columns['name']);
                            $calories  = $cell($columns['calories']);
                            $unit_raw  = $cell($columns['unit']);
                            $food_type = $cell($columns['type']);
                        } else {
                            // No usable header row - fall back to the original
                            // positional guess so older sheets still import.
                            $food_name = isset($rowData[0]) ? trim((string)$rowData[0]) : '';
                            $val1 = isset($rowData[1]) ? trim((string)$rowData[1]) : '';
                            $val2 = isset($rowData[2]) ? trim((string)$rowData[2]) : '';

                            // User's file has [Food Name, Quantity, Calories]
                            // Standard layout is [Food Name, Calories, Food Type]
                            if (is_numeric($val2) && !is_numeric($val1)) {
                                $calories = $val2;
                                $food_type = '';
                            } else {
                                $calories = $val1;
                                $food_type = $val2;
                            }
                        }

                        // Skip empty rows
                        if (empty($food_name)) {
                            continue;
                        }

                        // Get food_type_id if food_type name is provided
                        $food_type_id = '';
                        if (!empty($food_type) && isset($food_types_cache[strtolower($food_type)])) {
                            $food_type_id = $food_types_cache[strtolower($food_type)];
                        }

                        // Only accept units from the agreed list. Anything else is
                        // reported back rather than silently defaulted, so typos
                        // are visible to whoever uploaded the sheet.
                        $unit = null;
                        if ($unit_raw !== '') {
                            $unit = normalizeFoodUnit($unit_raw);
                            if ($unit === null && count($unitRejects) < 5) {
                                $unitRejects[$unit_raw] = $row;
                            }
                        }

                        // Check if food already exists
                        $name_key = strtolower($food_name);
                        $existing_id = isset($existing_food_cache[$name_key]) ? $existing_food_cache[$name_key] : null;

                        if ($existing_id === null) {
                            $insert_data = array(
                                'food_name' => $food_name,
                                'calories' => (int)$calories,
                                'unit' => $unit,
                                'food_type_id' => $food_type_id,
                                'status' => 'Active',
                                'created_on' => date('Y-m-d'),
                                'created_by' => session()->get('userid'),
                                'updated_on' => date('Y-m-d'),
                                'updated_by' => session()->get('userid')
                            );

                            try {
                                $id = $this->common_model->addData('food', $insert_data);
                                if ($id > 0) {
                                    $insertCount++;
                                    // Remember it, so a repeated name later in the
                                    // same sheet updates instead of inserting twice.
                                    $existing_food_cache[$name_key] = $id;
                                } else {
                                    $errorCount++;
                                    $lastError = "Failed to insert row $row";
                                }
                            } catch (\Exception $e) {
                                $errorCount++;
                                $lastError = $e->getMessage();
                            }
                        } else {
                            // Food exists! Update if the new row has valid calories
                            if ((int)$calories > 0) {
                                $update_data = [
                                    'calories' => (int)$calories,
                                    'updated_on' => date('Y-m-d'),
                                    'updated_by' => session()->get('userid')
                                ];
                                if ($food_type_id !== '') {
                                    $update_data['food_type_id'] = $food_type_id;
                                }
                                // A blank Unit cell must never wipe a stored unit.
                                if ($unit !== null) {
                                    $update_data['unit'] = $unit;
                                }
                                $this->common_model->updateData('food', $update_data, ['food_id' => $existing_id]);
                                $updateCount++;
                            } else {
                                $skipCount++;
                            }
                        }
                    }

                    $message = $insertCount . ' new food items added successfully!';
                    if ($updateCount > 0) {
                        $message .= ' ' . $updateCount . ' items updated with new calories.';
                    }
                    if ($skipCount > 0) {
                        $message .= ' ' . $skipCount . ' items skipped (already exist).';
                    }
                    if (!empty($unitRejects)) {
                        $parts = [];
                        foreach ($unitRejects as $badUnit => $badRow) {
                            $parts[] = '"' . $badUnit . '" (row ' . $badRow . ')';
                        }
                        $message .= ' Unrecognised unit ignored: ' . implode(', ', $parts)
                                  . '. Pick a unit from the dropdown in the template.';
                    }
                    if ($errorCount > 0) {
                        $message .= ' ' . $errorCount . ' items failed. Last error: ' . $lastError;
                    }

                    $arr_msg = array('success' => true, 'msg' => $message);
                } catch (\Exception $e) {
                    $arr_msg = array('success' => false, 'msg' => 'Error processing file: ' . $e->getMessage());
                }
            } else {
                $arr_msg = array('success' => false, 'msg' => 'Error uploading the file.');
            }
        } else {
            $arr_msg = array('success' => false, 'msg' => 'Invalid method.');
        }
        return $this->response->setJSON($arr_msg);
    }
}
