<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class AdminUsersSeeder extends Seeder
{
    public function run()
    {
        $email = env('ADMIN_EMAIL', 'admin@eavesdroid.com');
        $password = env('ADMIN_PASSWORD', 'AdminSecurePass@2026!');
        $username = env('ADMIN_USERNAME', 'admin');

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
            echo "Created admin: {$email}\n";
        } else {
            $user->password = $password;
            $user->active = 1;
            $userModel->save($user);
            echo "Updated admin password and active status for: {$email}\n";
        }

        setUserGroup((int) $user->id, 'admin');
        $user->addPermission('admin.access', 'users.create', 'users.edit', 'users.delete', 'beta.access');

        echo "Admin group and permissions ensured for: {$email}\n";
    }
}
