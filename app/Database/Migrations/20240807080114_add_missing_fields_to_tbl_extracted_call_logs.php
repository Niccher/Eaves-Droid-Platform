<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingFieldsToTblLogs extends Migration
{
    public function up()
    {
        $fields = [
            'country_iso' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'formatted_duration'
            ],
            'geocoded_location' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'country_iso'
            ],
            'number_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'geocoded_location'
            ],
            'number_type' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
                'after'      => 'number_label'
            ],
            'matched_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'number_type'
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
                'after'      => 'matched_number'
            ],
            'features' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
                'after'      => 'is_read'
            ],
            'data_usage' => [
                'type'       => 'BIGINT',
                'null'       => true,
                'default'    => 0,
                'after'      => 'features'
            ],
            'phone_account_component_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'data_usage'
            ],
            'phone_account_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'phone_account_component_name'
            ]
        ];

        $this->forge->addColumn('tbl_extracted_call_logs', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_extracted_call_logs', [
            'country_iso',
            'geocoded_location',
            'number_label',
            'number_type',
            'matched_number',
            'is_read',
            'features',
            'data_usage',
            'phone_account_component_name',
            'phone_account_id'
        ]);
    }
}
