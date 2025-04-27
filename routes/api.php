<?php

use Illuminate\Http\Request;
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
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['roles.permissions']);
    });
    
    Route::post('/logout', [LoginController::class, 'logout']);

    // Role management (accessible to any authenticated user)
    Route::apiResource('roles', RoleController::class);
    Route::get('/permissions', [RoleController::class, 'getAllPermissions']);

    // User management (accessible only to admin)
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('users', UserController::class);
        Route::get('/all-roles', [UserController::class, 'getAllRoles']);
    });

    // Categories
    Route::apiResource('categories', CategoryController::class);
    Route::get('/categories/{category}/audits', [CategoryController::class, 'audits']);
    Route::get('/categories/export/excel', [CategoryController::class, 'export']);
    Route::post('/categories/import/excel', [CategoryController::class, 'import']);
    Route::get('/categories/export/queue', [CategoryController::class, 'queueExport']);
    Route::post('/categories/import/queue', [CategoryController::class, 'queueImport']);

    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::get('/projects/{project}/audits', [ProjectController::class, 'audits']);
    Route::get('/projects/export/excel', [ProjectController::class, 'export']);
    Route::post('/projects/import/excel', [ProjectController::class, 'import']);
    Route::get('/projects/export/queue', [ProjectController::class, 'queueExport']);
    Route::post('/projects/import/queue', [ProjectController::class, 'queueImport']);
    Route::get('/categories-for-select', [ProjectController::class, 'getCategories']);

    // Tasks
    Route::apiResource('tasks', TaskController::class);
    Route::get('/tasks/{task}/audits', [TaskController::class, 'audits']);
    Route::get('/tasks/export/excel', [TaskController::class, 'export']);
    Route::post('/tasks/import/excel', [TaskController::class, 'import']);
    Route::get('/tasks/export/queue', [TaskController::class, 'queueExport']);
    Route::post('/tasks/import/queue', [TaskController::class, 'queueImport']);
    Route::get('/projects-for-select', [TaskController::class, 'getProjects']);
});
