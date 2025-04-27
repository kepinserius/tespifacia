<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL older than 5.7.7
        Schema::defaultStringLength(191);
        
        // Menekan warning PHP deprecated untuk Spatie Permission
        if (PHP_VERSION_ID >= 80100) {
            // Menekan warning untuk PHP 8.1+
            error_reporting(E_ALL & ~E_DEPRECATED);
        }
        
        // Register custom Blade directives
        Blade::directive('role', function ($role) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });
        
        Blade::directive('endrole', function () {
            return "<?php endif; ?>";
        });
        
        Blade::directive('permission', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->hasPermissionTo({$permission})): ?>";
        });
        
        Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });
        
        // Queue event listeners for logging
        Queue::before(function (JobProcessing $event) {
            logger()->info('Processing job: ' . $event->job->resolveName());
        });
        
        Queue::after(function (JobProcessed $event) {
            logger()->info('Job processed: ' . $event->job->resolveName());
        });
    }
}
