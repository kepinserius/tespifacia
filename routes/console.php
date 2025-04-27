<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:setup', function () {
    $this->info('Setting up the application...');
    
    $this->call('migrate:fresh');
    $this->call('db:seed');
    $this->call('storage:link');
    
    $this->info('Application setup completed successfully!');
})->purpose('Setup the application with fresh migrations and seed data');
