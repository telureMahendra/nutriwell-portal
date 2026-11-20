<?php

namespace App\Models;

use CodeIgniter\Model;

class Login_mod extends Model
{
    protected $table = 'users'; // Replace 'users' with your actual table name

    public function check_login($username, $password)
    {
        $builder = $this->db->table('users'); // Replace 'users' with your actual table name
        $builder->select('uid,username, first_name, last_name, user_role,isRoot');
        $builder->where('username', $username);
        $builder->where('pass', md5($password));
        $builder->where('user_status', 'active');
        $builder->limit(1);
        $query = $builder->get();
        // echo  $this->db->getLastQuery();;die;
        // echo "<pre>";print_r($query);die();
        if ($query->getNumRows() > 0) {
            $row = $query->getRow();

            $arr_session = [
                "userid" => $row->uid,
                "fullname" => $row->first_name . ' ' . $row->last_name,
                "username" => $row->username,
                "transaction_date" => date('Y-m-d'),
                "userrole" => $row->user_role,
                "usertype" => "admin",
                "isRoot" => $row->isRoot
            ];

            $session = session();
            $session->set($arr_session);

            $roleArray  = [];
            $menuArray  = [];
            $menuSql   = "SELECT * FROM adminmenu WHERE parentID=0 AND isActive=1 AND isDeleted=0 ORDER BY displayOrder ASC";
            $menuQuery = $this->db->query($menuSql);
            $menuResult = $menuQuery->getResultArray();
            if ($menuResult) {
                $pNo = 0;
                foreach ($menuResult as $menu) {
                    //$subSql   = "SELECT * FROM adminmenu WHERE parentID=".$menu["menuID"]." AND displayOrder > 0 AND isActive=1 AND isDeleted=0 ORDER BY displayOrder ASC";
                    $subSql   = "SELECT userrole.rID,adminmenu.* FROM userrole INNER JOIN adminmenu ON userrole.menuID=adminmenu.menuID WHERE adminmenu.parentID=" . $menu["menuID"] . " AND userrole.userID='" . $row->uid . "' AND adminmenu.isActive=1 AND adminmenu.isDeleted=0 ORDER BY adminmenu.displayOrder ASC";
                    $subQuery = $this->db->query($subSql);
                    $subResult = $subQuery->getResultArray();
                    if ($subResult) {
                        $subArray = [];
                        foreach ($subResult as $sub) {
                            if ($sub["displayOrder"] > 0) {
                                $menu["sub"][] = $sub;
                            }
                            $subArray[] = $sub["methodName"];
                        }


                        $menuArray[] =  $menu;
                        $roleArray[$menu["controller"]] = $subArray;
                    }
                }

                $m["menuArray"] = $menuArray;
                $m["roleArray"] = $roleArray;


                //print'<pre>';
                //print_r($roleArray);
                //print_r($menuArray);
                //die();

                $session->set($m);
            }
            return true;
        } else {
            return false;
        }
    }

    public function add_user_log($data)
    {
        $builder = $this->db->table('user_logs'); // Replace 'user_logs' with your actual table name

        $builder->insert($data);

        return $this->db->insertID(); // Use insertID() to retrieve the insert ID
    }


    public function change_password($userid, $old_pwd, $new_pwd)
    {
        $data = [
            'password' => md5($new_pwd),
        ];

        $builder = $this->db->table($this->table);
        $builder->where('id', $userid);
        $builder->where('password', md5($old_pwd));

        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            $builder->where('id', $userid);
            $builder->update($data);

            if (isset($_COOKIE['user_pwd'])) {
                setcookie('user_pwd', $new_pwd, time() + (30 * 60 * 60 * 24));
            }

            return true;
        } else {
            return false;
        }
    }
}
