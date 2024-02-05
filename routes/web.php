<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FileController;
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

//AddTaskfile 
Route::post('/upload-files/{task}', [FileController::class, 'store'])->name('file.store');
//AddProjectFile
Route::post('/upload-files/{project}', [FileController::class, 'ProjectFile'])->name('file.ProjectFile');

require __DIR__.'/auth.php';
