<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return view('app');
});

// SPA fallback route - Semua rute yang tidak cocok akan diarahkan ke aplikasi Vue
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Authentication routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    });

    // Role management (accessible to any authenticated user)
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{role}', [RoleController::class, 'show']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
    Route::get('/permissions', [RoleController::class, 'getAllPermissions']);

    // User management (accessible only to admin)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::get('/all-roles', [UserController::class, 'getAllRoles']);
    });

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    Route::get('/categories/{category}/audits', [CategoryController::class, 'audits']);
    Route::get('/categories/export/excel', [CategoryController::class, 'export']);
    Route::post('/categories/import/excel', [CategoryController::class, 'import']);
    Route::get('/categories/export/queue', [CategoryController::class, 'queueExport']);
    Route::post('/categories/import/queue', [CategoryController::class, 'queueImport']);

    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
    Route::get('/projects/{project}/audits', [ProjectController::class, 'audits']);
    Route::get('/projects/export/excel', [ProjectController::class, 'export']);
    Route::post('/projects/import/excel', [ProjectController::class, 'import']);
    Route::get('/projects/export/queue', [ProjectController::class, 'queueExport']);
    Route::post('/projects/import/queue', [ProjectController::class, 'queueImport']);
    Route::get('/categories-for-select', [ProjectController::class, 'getCategories']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::get('/tasks/{task}/audits', [TaskController::class, 'audits']);
    Route::get('/tasks/export/excel', [TaskController::class, 'export']);
    Route::post('/tasks/import/excel', [TaskController::class, 'import']);
    Route::get('/tasks/export/queue', [TaskController::class, 'queueExport']);
    Route::post('/tasks/import/queue', [TaskController::class, 'queueImport']);
    Route::get('/projects-for-select', [TaskController::class, 'getProjects']);
});

// Fallback route for SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*')->middleware('auth');
