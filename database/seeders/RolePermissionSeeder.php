<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. Clear cached roles and permissions ---
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- 2. Define permissions ---
        $permissions = [
            'manage users',
            'view tickets',
            'respond tickets',
            'close tickets',
            'view dashboard',
            'create tickets',
            'comment tickets',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- 3. Create roles ---
        $adminRole   = Role::firstOrCreate(['name' => 'admin']);
        $supportRole = Role::firstOrCreate(['name' => 'support']);
        $userRole    = Role::firstOrCreate(['name' => 'user']); // 👈 new role

        // --- 4. Assign permissions ---
        // Admin gets all permissions
        $adminRole->syncPermissions(Permission::all());

        // Support role: limited operational permissions
        $supportRole->syncPermissions([
            'view tickets',
            'respond tickets',
            'close tickets',
        ]);

        // User role: basic permissions
        $userRole->syncPermissions([
            'create tickets',
            'comment tickets',
            'view tickets',
        ]);

        // --- 5. Create default users ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        $support = User::firstOrCreate(
            ['email' => 'support@example.com'],
            [
                'name' => 'Support Staff',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        $normalUser = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        // --- 6. Assign roles ---
        $admin->assignRole($adminRole);
        $support->assignRole($supportRole);
        $normalUser->assignRole($userRole);

        // --- 7. Output info ---
        $this->command->info('✅ Roles, permissions, and default users have been seeded successfully.');
        $this->command->info('Admin login:   admin@example.com / password');
        $this->command->info('Support login: support@example.com / password');
        $this->command->info('User login:    user@example.com / password');
    }
}
