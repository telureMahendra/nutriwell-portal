<?php

namespace App\Models;

use CodeIgniter\Model;

class Testimonial_model extends Model
{
    protected $table = 'testimonial';
    protected $primaryKey = 'testimonial_id';
    protected $allowedFields = [
        'client_id', 'client_name', 'designation', 'description',
        'image_path', 'before_image', 'after_image', 'status',
        'approval_status', 'testimonial_type', 'date', 'rating',
        'created_on', 'created_by', 'updated_on', 'updated_by'
    ];

    // Get all testimonials with filters
    public function getTestimonials($type = null, $approval_status = null)
    {
        $builder = $this->db->table('testimonial AS T');
        $builder->select('T.*');
        
        if ($type) {
            $builder->where('T.testimonial_type', $type);
        }
        
        if ($approval_status) {
            $builder->where('T.approval_status', $approval_status);
        } else {
            // Show all for admin dashboard
            $builder->where('T.status !=', 'Deleted');
        }
        
        $builder->orderBy('T.created_on', 'DESC');
        $query = $builder->get();
        
        return $query->getResult();
    }

    // Get testimonials with CI4 server-side pagination
    public function getPaginatedTestimonials($type = null, $approval_status = null, $perPage = 10)
    {
        $this->select('*');
        $this->where('status !=', 'Deleted');
        if ($type) {
            $this->where('testimonial_type', $type);
        }
        if ($approval_status) {
            $this->where('approval_status', $approval_status);
        }
        $this->orderBy('created_on', 'DESC');
        
        return $this->asObject()->paginate($perPage);
    }

    // Get testimonials for public display
    public function getActiveTestimonials($type = null, $limit = null)
    {
        $builder = $this->db->table('testimonial AS T');
        $builder->select('T.*');
        $builder->where('T.status', 'Active');
        $builder->where('T.approval_status', 'Approved');
        
        if ($type) {
            $builder->where('T.testimonial_type', $type);
        }
        
        $builder->orderBy('T.date', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        $query = $builder->get();
        return $query->getResult();
    }

    public function getRandomTestimonials($type, $limit = 5)
    {
        $builder = $this->db->table('testimonial AS T');
        $builder->select('T.testimonial_id, T.client_name, T.designation, T.description, T.rating, T.date, T.before_image, T.after_image, T.image_path');
        $builder->where('T.status', 'Active');
        $builder->where('T.approval_status', 'Approved');
        $builder->where('T.testimonial_type', $type);
        $builder->orderBy('RAND()');
        $builder->limit($limit);
        return $builder->get()->getResult();
    }

    // Add testimonial with approval status
    public function addTestimonial($insertData)
    {
        $builder = $this->db->table('testimonial');
        $builder->insert($insertData);
        return $this->db->insertID();
    }

    // Update approval status
    // Update approval status
public function updateApproval($testimonial_id, $approval_status, $comments = '')
{
    $builder = $this->db->table('testimonial');
    $builder->where('testimonial_id', $testimonial_id);
    
    $update_data = [
        'approval_status' => $approval_status,
        'updated_on' => date('Y-m-d H:i:s'),
        'updated_by' => session()->get('userid')
    ];
    
    $builder->update($update_data);
    
    // Return 1 for success, 0 for failure
    return $this->db->affectedRows() > 0 ? 1 : 0;
}

    // Get pending testimonials count
    public function getPendingCount()
    {
        $builder = $this->db->table('testimonial');
        $builder->where('approval_status', 'Pending');
        $builder->where('status !=', 'Deleted');
        return $builder->countAllResults();
    }
}