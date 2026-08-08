<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAdminReports extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'user_scope' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'default'    => 'all',
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'date_from' => [
                'type'       => 'DATE',
                'null'       => true,
            ],
            'date_to' => [
                'type'       => 'DATE',
                'null'       => true,
            ],
            'data_types' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'data_types_labels' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'format' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'default'    => 'html',
            ],
            'record_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => false,
            ],
            'file_size' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('created_by', false, false, 'created_by');
        $this->forge->addKey('created_at', false, false, 'created_at');

        $this->forge->createTable('tbl_admin_reports', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_admin_reports', true);
    }
}
