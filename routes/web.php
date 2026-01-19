<?php

use Inertia\Inertia;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProjectController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
Route::post('/staff/store', [StaffController::class, 'store'])->name('staff.store');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
Route::post('/projects/create', [ProjectController::class, 'store'])->name('projects.store');
Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
Route::get('/projects/{id}/edit', [ProjectController::class, 'edit'])->name('project.edit');

Route::get('/tasks', [TaskController::class, 'index'])->name('task.index');
Route::get('/tasks/create', [TaskController::class, 'create'])->name('task.create');
Route::post('/tasks/create', [TaskController::class, 'store'])->name('task.store');
Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('task.show');
Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('task.update');
Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('task.destroy');
Route::get('/api/tasks/project/{projectId}', [TaskController::class, 'getByProject'])->name('task.by-project');

Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
Route::post('/staff/create', [StaffController::class, 'store'])->name('staff.store');
Route::get('/staff/{id}', [StaffController::class, 'show'])->name('staff.show');
Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
