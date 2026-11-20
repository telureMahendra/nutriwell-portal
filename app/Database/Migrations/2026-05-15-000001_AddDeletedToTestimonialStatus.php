<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedToTestimonialStatus extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE testimonial MODIFY COLUMN status ENUM('Pending','Active','Inactive','Deleted') DEFAULT 'Pending'");

        // Fix any rows where a previous soft-delete attempt stored '' (invalid enum value)
        $this->db->query("UPDATE testimonial SET status = 'Deleted' WHERE status = '' OR status IS NULL");
    }

    public function down()
    {
        // Permanently remove Deleted rows before reverting enum
        $this->db->query("DELETE FROM testimonial WHERE status = 'Deleted'");
        $this->db->query("ALTER TABLE testimonial MODIFY COLUMN status ENUM('Pending','Active','Inactive') DEFAULT 'Pending'");
    }
}
