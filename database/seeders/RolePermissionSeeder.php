<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            
            // Category management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'export categories',
            'import categories',
            
            // Project management
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',
            'export projects',
            'import projects',
            
            // Task management
            'view tasks',
            'create tasks',
            'edit tasks',
            'delete tasks',
            'export tasks',
            'import tasks',
            
            // Audit management
            'view audits',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'uuid' => (string) Str::uuid(),
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'admin',
            'guard_name' => 'web'
        ]);
        $adminRole->givePermissionTo(Permission::all());

        $managerRole = Role::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'manager',
            'guard_name' => 'web'
        ]);
        $managerRole->givePermissionTo([
            'view users',
            'view roles',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'export categories',
            'import categories',
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',
            'export projects',
            'import projects',
            'view tasks',
            'create tasks',
            'edit tasks',
            'delete tasks',
            'export tasks',
            'import tasks',
            'view audits',
        ]);

        $userRole = Role::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'user',
            'guard_name' => 'web'
        ]);
        $userRole->givePermissionTo([
            'view categories',
            'view projects',
            'create projects',
            'edit projects',
            'export projects',
            'view tasks',
            'create tasks',
            'edit tasks',
            'export tasks',
            'view audits',
        ]);
    }
}
