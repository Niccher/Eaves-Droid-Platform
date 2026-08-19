<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveProcessesFromFeatureTiers extends Migration
{
    public function up()
    {
        $this->db->table('tbl_feature_tiers')->whereIn('slug', ['processes', 'running_processes'])->delete();
    }

    public function down()
    {
        $this->db->table('tbl_feature_tiers')->insertBatch([
            [
                'category_type' => 'hardware',
                'slug'          => 'processes',
                'label'         => 'Running Processes',
                'description'   => 'Process snapshots',
                'icon'          => 'fas fa-tasks',
                'color_class'   => 'card-dark',
                'bg_class'      => 'bg-dark',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'software',
                'slug'          => 'running_processes',
                'label'         => 'Running Processes',
                'description'   => 'Detailed process list with memory and CPU',
                'icon'          => 'fas fa-cogs',
                'color_class'   => 'card-success',
                'bg_class'      => 'bg-success',
                'required_tier' => 'platinum',
            ],
        ]);
    }
}
