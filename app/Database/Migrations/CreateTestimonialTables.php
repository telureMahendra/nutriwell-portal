<?php
// File: app/Database/Migrations/CreateTestimonialTables.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTestimonialTables extends Migration
{
    public function up()
    {
        // Add columns to testimonial table
        $this->forge->addColumn('testimonial', [
            'rating' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 5,
                'after' => 'description'
            ],
            'designation' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'client_name'
            ],
            'testimonial_type' => [
                'type' => 'ENUM',
                'constraint' => ['text', 'image'],
                'default' => 'text',
                'after' => 'status'
            ],
            'image_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'after' => 'description'
            ]
        ]);

        // Modify status column
        $this->forge->modifyColumn('testimonial', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Pending', 'Active', 'Inactive'],
                'default' => 'Pending'
            ]
        ]);

        // Create testimonial_images table
        $this->forge->addField([
            'image_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'testimonial_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'image_type' => [
                'type' => 'ENUM',
                'constraint' => ['before', 'after']
            ],
            'image_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500
            ],
            'created_on' => [
                'type' => 'DATE'
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11
            ]
        ]);
        $this->forge->addKey('image_id', true);
        $this->forge->addForeignKey('testimonial_id', 'testimonial', 'testimonial_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('testimonial_images');

        // Create testimonial_approval_log table
        $this->forge->addField([
            'log_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'testimonial_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'old_status' => [
                'type' => 'ENUM',
                'constraint' => ['Pending', 'Active', 'Inactive']
            ],
            'new_status' => [
                'type' => 'ENUM',
                'constraint' => ['Pending', 'Active', 'Inactive']
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'changed_by' => [
                'type' => 'INT',
                'constraint' => 11
            ],
            'changed_on' => [
                'type' => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP'
            ]
        ]);
        $this->forge->addKey('log_id', true);
        $this->forge->addForeignKey('testimonial_id', 'testimonial', 'testimonial_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('testimonial_approval_log');
    }

    public function down()
    {
        $this->forge->dropTable('testimonial_approval_log');
        $this->forge->dropTable('testimonial_images');
        
        // Remove added columns
        $this->forge->dropColumn('testimonial', ['rating', 'designation', 'testimonial_type', 'image_path']);
        
        // Revert status column
        $this->forge->modifyColumn('testimonial', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Active', 'Inactive'],
                'default' => 'Active'
            ]
        ]);
    }
}