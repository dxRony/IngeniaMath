<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Moderator\ModeratorDashboardController;
use App\Http\Controllers\Moderator\Resources\ResourceReviewController;
use App\Http\Controllers\Register\RegisterController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Tutor\TutorDashboardController;

use App\Http\Controllers\Tutor\Resources\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login.index');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'index'])->name('register.index');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Admin
Route::prefix('admin')->middleware(['auth', 'role:1'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

// Moderator
Route::prefix('moderator')->middleware(['auth', 'role:2'])->group(function () {
    Route::get('/dashboard', [ModeratorDashboardController::class, 'index'])->name('moderator.dashboard');

    // Revision de recursos educativos
    Route::prefix('resources')->name('moderator.resources.')->group(function () {
        Route::get('/',              [ResourceReviewController::class, 'index'])->name('index');
        Route::get('/{id}',          [ResourceReviewController::class, 'show'])->name('show')->whereNumber('id');
        Route::post('/{id}/approve', [ResourceReviewController::class, 'approve'])->name('approve')->whereNumber('id');
        Route::post('/{id}/reject',  [ResourceReviewController::class, 'reject'])->name('reject')->whereNumber('id');
    });
});

// Tutor
Route::prefix('tutor')->middleware(['auth', 'role:3'])->group(function () {
    Route::get('/dashboard', [TutorDashboardController::class, 'index'])->name('tutor.dashboard');

    Route::prefix('resources')->name('tutor.resources.')->group(function () {
        Route::get('/',                [ResourceController::class, 'index'])->name('index');
        Route::get('/create',          [ResourceController::class, 'create'])->name('create');
        Route::post('/',               [ResourceController::class, 'store'])->name('store');


    });
});



// Student
Route::prefix('student')->middleware(['auth', 'role:4'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
});
