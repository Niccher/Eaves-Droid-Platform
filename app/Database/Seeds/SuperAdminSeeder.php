<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $email = env('SUPERADMIN_EMAIL', 'superadmin@eavesdroid.com');
        $password = env('SUPERADMIN_PASSWORD', 'SuperAdmin@2024!');
        $username = env('SUPERADMIN_USERNAME', 'superadmin');

        $userModel = model(UserModel::class);

        $user = $userModel->findByCredentials(['email' => $email]);

        if ($user === null) {
            $user = new User([
                'email'    => $email,
                'username' => $username,
                'password' => $password,
                'active'   => 1,
            ]);

            if (! $userModel->save($user)) {
                echo "Failed to create {$email}: " . json_encode($userModel->errors()) . "\n";

                return;
            }

            $user = $userModel->findByCredentials(['email' => $email]);
            echo "Created superadmin: {$email}\n";
        } else {
            echo "User {$email} already exists, skipping creation.\n";
        }

        if (! $user->active) {
            $userModel->update($user->id, ['active' => 1]);
            echo "Activated superadmin: {$email}\n";
        }

        setUserGroup((int) $user->id, 'superadmin');
        $user->addPermission(
            'admin.access',
            'admin.settings',
            'users.manage-admins',
            'users.manage-roles',
            'users.create',
            'users.edit',
            'users.delete',
            'security.audit',
            'system.migrate',
            'system.seed',
            'system.env',
            'beta.access',
            'intelligence.search',
            'intelligence.impersonate'
        );

        echo "Superadmin group and permissions ensured for: {$email}\n";
    }
}
