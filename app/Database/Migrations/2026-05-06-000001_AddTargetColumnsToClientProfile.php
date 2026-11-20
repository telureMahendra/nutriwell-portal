<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTargetColumnsToClientProfile extends Migration
{
    public function up()
    {
        $this->forge->addColumn('client_profile', [
            'target_water' => [
                'type'       => 'INT',
                'default'    => 8,
                'null'       => false,
                'after'      => 'target_weight',
            ],
            'target_sleep' => [
                'type'       => 'INT',
                'default'    => 480,
                'null'       => false,
                'after'      => 'target_water',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('client_profile', ['target_water', 'target_sleep']);
    }
}
