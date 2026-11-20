<?php
namespace App\Models;
use CodeIgniter\Model;

class Common_model extends Model
{
    public function getData($tableName, $select, $joinTable, $joinCondition, $condition, $groupby, $orderby, $limit) {
        $builder = $this->db->table($tableName);
        $builder->select($select);

        if (!empty($joinTable)) {
            $builder->join($joinTable,$joinCondition);
        }
        if (!empty($condition)) {
            $builder->where($condition);
        }
        if (!empty($groupby)) {
            $builder->groupBy($groupby);
        }
        if (!empty($orderby)) {
            $builder->orderBy($orderby);
        }
        if ($limit !='') {
            $builder->limit($limit);
        }
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }


    public function addData($tableName, $insert_data) {
        $builder = $this->db->table($tableName);
        $builder->insert($insert_data);
        return $this->db->insertID();
    }

    public function getAllData($tableName, $condition) {
        $builder = $this->db->table($tableName);
        $builder->select('*');
        if (!empty($condition)) {
            $builder->where($condition);
        }
        $query = $builder->get();
        // echo $this->db->getLastQuery();die();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function updateData($tableName, $update_data, $condition) {
        $builder = $this->db->table($tableName);
        $builder->where($condition);
        $res = $builder->update($update_data);
        //$affectedRows = $this->db->affectedRows();
        //if ($affectedRows > 0) {
        if($res){
            return 1;
        } else {
            return 0;
        }
    }

    public function addPlan($insert_data,$insert_feature){
        $builder = $this->db->table("plan");
        $builder->insert($insert_data);
        $plan_id=$this->db->insertID();

        $totalrows = sizeof($insert_feature['feature_name']);
        for($i=0 ; $i<$totalrows; $i++)
        {
            if($insert_feature['feature_name']!= '')
            {
                $image=$insert_feature['feature_image'][$i];
                $feature_image=$image->getName('feature_img');
                $insert_feature_plan  = [
                    'plan_id' => $plan_id,
                    'feature_name' => $insert_feature['feature_name'][$i],
                    'feature_caption' => $insert_feature['feature_caption'][$i],
                    'feature_image' => $feature_image,
                    'status' => $insert_feature['status'],
                    'created_on' => $insert_feature['created_on'],
                    'created_by' => $insert_feature['created_by'],
                    'updated_on' => $insert_feature['updated_on'],
                    'updated_by' => $insert_feature['updated_by'],
                ];
                $builder = $this->db->table("plan_features");
                $builder->insert($insert_feature_plan);
                $plans_id=$this->db->insertID();
            }
        }
        return $this->db->insertID();
    }

    public function deleteFeature($condition) {
        $builder = $this->db->table("plan_features");
        $builder->where($condition);
        $builder->delete();
        $affectedRows = $this->db->affectedRows();
        if ($affectedRows > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function updatePlanData($update_Plan_data, $update_feature,$plan_id) {
        $builder = $this->db->table("plan");
        $condition = ['plan_id' => $plan_id];
        $builder->where( $condition);
        $id=$builder->update($update_Plan_data);
        $key = 0;
        if($id>0)
        {
            $update_plan_feature=[];
            $totalrows = sizeof( $update_feature['feature_name']);
            for($i=0 ; $i<$totalrows; $i++)
            {
                $key = $i;
                if(!empty($update_feature['pf_id'][$i])) 
                {
                    $update_plan_feature['feature_image']=$update_feature['feature_image'][$i];
                    $update_plan_feature['feature_name']=$update_feature['feature_name'][$i];
                    $update_plan_feature['feature_caption']=$update_feature['feature_caption'][$i];
                    $update_plan_feature['status']=$update_feature['status'];
                    $update_plan_feature['created_on']=$update_feature['created_on'];
                    $update_plan_feature['created_by']=$update_feature['created_by'];
                    $update_plan_feature['updated_on']=$update_feature['updated_on'];
                    $update_plan_feature['updated_by']=$update_feature['updated_by'];

                    $condition1= ['pf_id' => $update_feature['pf_id'][$i]];
                    $builder1 = $this->db->table("plan_features");
                    $builder1->where( $condition1); 
                    $builder1->limit(1); 
                    $id1=$builder1->update($update_plan_feature);
                }else{
                    if ($update_feature['feature_image'][$i] !='') {
                        $insert_feature_plan  = [
                            'plan_id' => $plan_id,
                            'feature_name' => $update_feature['feature_name'][$i],
                            'feature_caption' => $update_feature['feature_caption'][$i],
                            'feature_image' => $update_feature['feature_image'][$i],
                            'status' => $update_feature['status'],
                            'created_on' => $update_feature['created_on'],
                            'created_by' => $update_feature['created_by'],
                            'updated_on' => $update_feature['updated_on'],
                            'updated_by' => $update_feature['updated_by'],
                        ];
                        $builder = $this->db->table("plan_features");
                        $builder->insert($insert_feature_plan);
                        $pf_id=$this->db->insertID();
                    }
                }
            } 
        }
        return $id;
    }
    public function deleteAppointmentcomment($condition) {
        $builder = $this->db->table("appointmentcomment");
        $builder->where($condition);
        $builder->delete();
        $affectedRows = $this->db->affectedRows();
        if ($affectedRows > 0) {
            return 1;
        } else {
            return 0;
        }
    }    

    public function deleteClientcomment($condition) {
        $builder = $this->db->table("clientcomment");
        $builder->where($condition);
        $builder->delete();
        $affectedRows = $this->db->affectedRows();
        if ($affectedRows > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    // In App\Models\Common_model.php
public function countRows($tableName, $condition = [])
{
    $builder = $this->db->table($tableName);
    $builder->select('COUNT(*) as total');
    
    if (!empty($condition)) {
        $builder->where($condition);
    }
    
    $query = $builder->get();
    $result = $query->getRow();
    
    return $result ? $result->total : 0;
}
}

