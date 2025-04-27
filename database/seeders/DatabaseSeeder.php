<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $managerRole = Role::create(['name' => 'manager']);
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
        ]);

        $userRole = Role::create(['name' => 'user']);
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
        ]);

        // Create admin user
        $admin = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ]);
        $admin->assignRole('admin');

        // Create manager user
        $manager = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ]);
        $manager->assignRole('manager');

        // Create regular user
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ]);
        $user->assignRole('user');

        // Create sample categories
        $categories = [
            [
                'name' => 'Web Development',
                'description' => 'Projects related to web development',
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'code', 'color' => 'blue']),
            ],
            [
                'name' => 'Mobile Development',
                'description' => 'Projects related to mobile app development',
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'smartphone', 'color' => 'green']),
            ],
            [
                'name' => 'Design',
                'description' => 'Design projects including UI/UX',
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'brush', 'color' => 'purple']),
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create([
                'uuid' => (string) Str::uuid(),
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'is_active' => $categoryData['is_active'],
                'metadata' => $categoryData['metadata'],
            ]);

            // Create sample projects for each category
            for ($i = 1; $i <= 2; $i++) {
                $project = Project::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => "{$categoryData['name']} Project {$i}",
                    'description' => "Sample project {$i} for {$categoryData['name']}",
                    'category_id' => $category->id,
                    'is_active' => true,
                    'start_date' => now(),
                    'end_date' => now()->addMonths(3),
                    'metadata' => json_encode(['priority' => $i === 1 ? 'high' : 'medium']),
                ]);

                // Create sample tasks for each project
                $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
                for ($j = 1; $j <= 3; $j++) {
                    Task::create([
                        'uuid' => (string) Str::uuid(),
                        'title' => "Task {$j} for {$project->name}",
                        'description' => "Sample task {$j} for {$project->name}",
                        'project_id' => $project->id,
                        'status' => $statuses[array_rand($statuses)],
                        'is_priority' => $j === 1,
                        'due_date' => now()->addDays(rand(1, 30)),
                        'metadata' => json_encode(['complexity' => rand(1, 5)]),
                    ]);
                }
            }
        }
    }
}
