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
        $password = env('ADMIN_PASSWORD', 'Admin@2024!');
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
            echo "User {$email} already exists, skipping creation.\n";
        }

        if (! $user->active) {
            $userModel->update($user->id, ['active' => 1]);
            echo "Activated admin: {$email}\n";
        }

        setUserGroup((int) $user->id, 'admin');
        $user->addPermission('admin.access', 'users.create', 'users.edit', 'users.delete', 'beta.access');

        echo "Admin group and permissions ensured for: {$email}\n";
    }
}
