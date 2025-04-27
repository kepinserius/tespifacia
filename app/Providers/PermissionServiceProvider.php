<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add UUID generation to Permission model
        Permission::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });

        // Add UUID generation to Role model
        Role::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }
}
