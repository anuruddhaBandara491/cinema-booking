<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'movies.view',
            'movies.manage',
            'reports.view',
            'tickets.manage',
            'logs.view',
            'bookings.view',
            'bookings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $userRole->syncPermissions([
            'movies.view',
            'bookings.view',
        ]);

        $managerRole->syncPermissions([
            'movies.view',
            'movies.manage',
            'reports.view',
            'bookings.view',
            'bookings.manage',
        ]);

        $adminRole->syncPermissions([
            'movies.view',
            'movies.manage',
            'reports.view',
            'tickets.manage',
            'logs.view',
            'bookings.view',
            'bookings.manage',
        ]);

        $testUser = User::where('email', 'test@example.com')->first();
        if ($testUser && ! $testUser->hasAnyRole(['user', 'manager', 'admin'])) {
            $testUser->assignRole('user');
        }
    }
}
