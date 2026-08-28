<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUuidToSupportMessages extends Migration
{
    public function up()
    {
        $this->forge->addColumn('support_messages', [
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        // Add unique index on uuid
        $this->db->query("CREATE UNIQUE INDEX support_messages_uuid_idx ON support_messages (uuid)");

        // Populate UUIDs for any existing records
        $db = \Config\Database::connect();
        $messages = $db->table('support_messages')->select('id')->get()->getResultArray();

        foreach ($messages as $msg) {
            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            $db->table('support_messages')->where('id', $msg['id'])->update(['uuid' => $uuid]);
        }

        // Change uuid to NOT NULL after populating
        $this->forge->modifyColumn('support_messages', [
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->db->query("DROP INDEX support_messages_uuid_idx ON support_messages");
        $this->forge->dropColumn('support_messages', 'uuid');
    }
}
