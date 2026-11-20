<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitToFood extends Migration
{
    public function up()
    {
        // Nullable on purpose: NULL means "never set", which is what tells the
        // API to derive the unit from the food name instead.
        $this->forge->addColumn('food', [
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'calories',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('food', 'unit');
    }
}
