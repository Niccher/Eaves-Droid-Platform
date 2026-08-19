<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveEmailFromFeatureTiers extends Migration
{
    public function up()
    {
        $this->db->table('tbl_feature_tiers')->where('slug', 'email')->delete();
    }

    public function down()
    {
        $this->db->table('tbl_feature_tiers')->insert([
            'category_type' => 'software',
            'slug'          => 'email',
            'label'         => 'Email Accounts',
            'description'   => 'Configured email accounts and providers',
            'icon'          => 'fas fa-envelope',
            'color_class'   => 'card-primary',
            'bg_class'      => 'bg-primary',
            'required_tier' => 'platinum',
        ]);
    }
}
