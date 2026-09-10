<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateTblExtractedMediaExifTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'media_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
            ],
            'date_added' => [
                'type'       => 'BIGINT',
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'file_size' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'folder_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('device_id');
        $this->forge->addKey('media_id');
        $this->forge->createTable('tbl_extracted_media_exif');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_extracted_media_exif');
    }
}
