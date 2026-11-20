<?php
namespace App\Models;

use CodeIgniter\Model;

class Notification_model extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title', 
        'message', 
        'image_url', 
        'deep_link',
        'type',
        'created_by',
        'scheduled_at',
        'sent_at',
        'status',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    // CREATE notification
    public function create_notification($data)
    {
        $this->insert($data);
        return $this->getInsertID();
    }

    
    // GET all notifications (DESC)
    public function get_all()
    {
        return $this->orderBy('id', 'DESC')
                    ->findAll();
    }

    // GET single notification
    public function get_by_id($id)
    {
        return $this->find($id);
    }

    

    // DELETE notification with audience data
    public function delete_notification_with_audience($id)
    {
        $db = \Config\Database::connect();
        
        // Start transaction to ensure both deletes happen
        $db->transStart();
        
        // Delete from notification_audience table
        $audienceBuilder = $db->table('notification_audience');
        $audienceBuilder->where('notification_id', $id);
        $audienceDeleted = $audienceBuilder->delete();
        
        // Delete from notifications table
        $notificationDeleted = $this->delete($id);
        
        $db->transComplete();
        
        return $notificationDeleted && $audienceDeleted;
    }

    // GET notifications with audience data
    public function get_notifications_with_audience()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('notifications n');
        $builder->select('n.*, na.audience_type, na.client_ids, na.created_at as audience_created');
        $builder->join('notification_audience na', 'n.id = na.notification_id', 'left');
        $builder->orderBy('n.id', 'DESC');
        
        return $builder->get()->getResult();
    }

    // GET single notification with audience data
    public function get_notification_with_audience($id)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('notifications n');
        $builder->select('n.*, na.audience_type, na.client_ids, na.created_at as audience_created');
        $builder->join('notification_audience na', 'n.id = na.notification_id', 'left');
        $builder->where('n.id', $id);
        
        return $builder->get()->getRow();
    }
}