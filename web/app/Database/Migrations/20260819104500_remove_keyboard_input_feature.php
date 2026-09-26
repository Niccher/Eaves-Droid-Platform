<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveKeyboardInputFeature extends Migration
{
    public function up()
    {
        $this->db->table('tbl_feature_tiers')->where('slug', 'keyboard_input')->delete();
    }

    public function down()
    {
        $this->db->table('tbl_feature_tiers')->insert([
            'category_type' => 'software',
            'slug'          => 'keyboard_input',
            'label'         => 'Keyboard Input',
            'description'   => 'Keystroke logs and type indicators',
            'icon'          => 'fas fa-keyboard',
            'color_class'   => 'card-secondary',
            'bg_class'      => 'bg-secondary',
            'required_tier' => 'platinum',
        ]);
    }
}
