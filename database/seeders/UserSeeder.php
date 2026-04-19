<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Demo User',
                'email' => 'user@example.com',
                'role' => 'user',
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'role' => 'manager',
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );

            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }
    }
}
