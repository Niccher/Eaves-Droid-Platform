<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('PlansSeeder');
        $this->call('PlanVersionsSeeder');
        $this->call('FeatureTiersSeeder');
        $this->call('CronJobsSeeder');
        $this->call('SystemVersionsSeeder');
        $this->call('SystemChangelogsSeeder');
        $this->call('AdminUsersSeeder');
        $this->call('SuperAdminSeeder');
        $this->call('DemoDataSeeder');
    }
}
