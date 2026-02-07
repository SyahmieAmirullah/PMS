<?php

use Inertia\Inertia;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PhaseController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\Auth\SessionController;

Route::middleware('guest:web,staff')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth:web,staff')
    ->name('logout');

Route::middleware('auth:web,staff')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Welcome');
    })->name('home');

    Route::get('dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/projects/{id}/edit', [ProjectController::class, 'edit'])->name('project.edit');

    Route::get('/phases', [PhaseController::class, 'index'])->name('phases.index');
    Route::get('/phases/create', [PhaseController::class, 'create'])->name('phases.create');
    Route::post('/phases', [PhaseController::class, 'store'])->name('phases.store');
    Route::get('/phases/{id}', [PhaseController::class, 'show'])->name('phases.show');
    Route::get('/phases/{id}/edit', [PhaseController::class, 'edit'])->name('phases.edit');
    Route::put('/phases/{id}', [PhaseController::class, 'update'])->name('phases.update');
    Route::delete('/phases/{id}', [PhaseController::class, 'destroy'])->name('phases.destroy');

    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/feedback/{id}', [FeedbackController::class, 'show'])->name('feedback.show');
    Route::delete('/feedback/{id}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');

    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/meetings/{id}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::get('/meetings/{id}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
    Route::put('/meetings/{id}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::delete('/meetings/{id}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

    Route::get('/tasks', [TaskController::class, 'index'])->name('task.index');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('task.create');
    Route::post('/tasks/create', [TaskController::class, 'store'])->name('task.store');
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('task.show');
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('task.update');
    Route::put('/tasks/{id}/done', [TaskController::class, 'markDone'])->name('task.done');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('task.destroy');
    Route::get('/api/tasks/project/{projectId}', [TaskController::class, 'getByProject'])->name('task.by-project');

    Route::middleware('role:Admin')->group(function () {
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff/create', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{id}', [StaffController::class, 'show'])->name('staff.show');
        Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
