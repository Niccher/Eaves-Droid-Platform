<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEnhancedSmsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            // Primary Key as requested
            'counter' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // ---------- Android JSON Mapping ----------
            'android_sms_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'sms_id from Android',
            ],
            'thread_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => false,
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'formatted_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'body' => [
                'type' => 'LONGTEXT',
                'null' => false,
                'comment' => 'Base64 string from Android',
            ],
            'body_length' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'sms_date' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'sms_date_sent' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'sms_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'type_code' => [
                'type'       => 'INT',
                'constraint' => 5,
            ],
            'is_read' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'is_seen' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'status_code' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'error_code' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'protocol' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'protocol_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'service_center' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_locked' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'creator' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            // ---------- Device & Ownership ----------
            'meta_owner' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            // ---------- Timestamps ----------
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addPrimaryKey('counter');

        // Ensure we don't duplicate the same SMS from the same device
        $this->forge->addUniqueKey(['android_sms_id', 'device_id', 'meta_owner']);

        $this->forge->addKey('address');
        $this->forge->addKey('sms_date');
        $this->forge->addKey('meta_owner');

        $this->forge->createTable('tbl_sms', true);

        // Add Fulltext for searching SMS content
        $this->db->query("CREATE FULLTEXT INDEX ft_sms_body ON tbl_sms(body)");
    }

    public function down()
    {
        $this->forge->dropTable('tbl_sms', true);
    }
}