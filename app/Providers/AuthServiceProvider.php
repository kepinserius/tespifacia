<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Category;
use App\Models\Project;
use App\Models\Task;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Register policies here if needed
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register Gates for admin access
        Gate::define('admin', function (User $user) {
            return $user->hasRole('admin');
        });

        // Register Gates for manager access
        Gate::define('manager', function (User $user) {
            return $user->hasRole('admin') || $user->hasRole('manager');
        });

        // Register Gates for specific resources
        Gate::define('manage-users', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('manage-roles', function (User $user) {
            return $user->hasRole('admin');
        });

        // Category gates
        Gate::define('view-categories', function (User $user) {
            return $user->hasPermissionTo('view categories');
        });

        Gate::define('create-categories', function (User $user) {
            return $user->hasPermissionTo('create categories');
        });

        Gate::define('update-categories', function (User $user, Category $category) {
            return $user->hasPermissionTo('edit categories');
        });

        Gate::define('delete-categories', function (User $user, Category $category) {
            return $user->hasPermissionTo('delete categories');
        });

        // Project gates
        Gate::define('view-projects', function (User $user) {
            return $user->hasPermissionTo('view projects');
        });

        Gate::define('create-projects', function (User $user) {
            return $user->hasPermissionTo('create projects');
        });

        Gate::define('update-projects', function (User $user, Project $project) {
            return $user->hasPermissionTo('edit projects');
        });

        Gate::define('delete-projects', function (User $user, Project $project) {
            return $user->hasPermissionTo('delete projects');
        });

        // Task gates
        Gate::define('view-tasks', function (User $user) {
            return $user->hasPermissionTo('view tasks');
        });

        Gate::define('create-tasks', function (User $user) {
            return $user->hasPermissionTo('create tasks');
        });

        Gate::define('update-tasks', function (User $user, Task $task) {
            return $user->hasPermissionTo('edit tasks');
        });

        Gate::define('delete-tasks', function (User $user, Task $task) {
            return $user->hasPermissionTo('delete tasks');
        });
    }
}
