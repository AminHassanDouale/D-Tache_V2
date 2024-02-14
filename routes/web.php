<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

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

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
Route::view('history', 'history')
    ->middleware(['auth'])
    ->name('history');

    // Project Index Page
    Route::view('projects', 'projects.index')
    ->middleware(['auth'])
    ->name('projects.index');
    Route::view('projects/create', 'projects.create')
    ->middleware(['auth'])
    ->name('projects.create'); 
    
    Volt::route('projects/{project}/edit', 'project.edit-project')
    ->middleware(['auth'])
    ->name('projects.edit');
  



    //tasks Index

    Route::view('tasks', 'tasks.index')
    ->middleware(['auth'])
    ->name('tasks.index');
    Volt::route('tasks/{task}/edit', 'task.edit-task')
    ->middleware(['auth'])
    ->name('tasks.edit');

    Volt::route('tasks/{task}/show', 'task.show-task')
    ->middleware(['auth'])
    ->name('tasks.show');


    //categories
    Route::resource('categories', CategoryController::class);
    //users
    Route::resource('users', UserController::class);
    //Report
    Route::view('report', 'reports.report')
    ->middleware(['auth', 'verified'])
    ->name('report');
    Route::view('report/department', 'reports.department')
    ->middleware(['auth', 'verified'])
    ->name('report.department');
    Route::view('report/dashboard', 'report.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('report.dashboard');
 





    //role 
    Route::get('roles', [RoleController::class, 'index'])->name('admin.roles.index');
    Route::get('roles/create', [RoleController::class, 'create'])->name('admin.roles.create');
    Route::post('roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('admin.roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');

//Permission
Route::get('permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
Route::get('permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create');
Route::post('permissions', [PermissionController::class, 'store'])->name('admin.permissions.store');
Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('admin.permissions.update');

Route::post('/roles/{role}/permissions', [RoleController::class, 'givePermission'])->name('admin.roles.permissions');
Route::delete('/roles/{role}/permissions/{permission}', [RoleController::class, 'revokePermission'])->name('admin.roles.permissions.revoke');
//Route::resource('/permissions', PermissionController::class);
Route::post('/permissions/{permission}/roles', [PermissionController::class, 'assignRole'])->name('admin.permissions.roles');
Route::delete('/permissions/{permission}/roles/{role}', [PermissionController::class, 'removeRole'])->name('admin.permissions.roles.remove');
Route::post('/users/{user}/roles', [UserController::class, 'assignRole'])->name('users.roles');
Route::delete('/users/{user}/roles/{role}', [UserController::class, 'removeRole'])->name('users.roles.remove');
Route::post('/users/{user}/permissions', [UserController::class, 'givePermission'])->name('users.permissions');
Route::delete('/users/{user}/permissions/{permission}', [UserController::class, 'revokePermission'])->name('users.permissions.revoke');
//AddTaskfile 
Route::post('/upload-files/{task}', [FileController::class, 'store'])->name('file.store');
//AddProjectFile
Route::post('/upload-files/{project}', [FileController::class, 'ProjectFile'])->name('file.ProjectFile');

require __DIR__.'/auth.php';
