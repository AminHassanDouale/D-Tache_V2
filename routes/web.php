<?php

use Illuminate\Support\Facades\Route;


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
  


require __DIR__.'/auth.php';
