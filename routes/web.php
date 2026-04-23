<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Moderator\ModeratorDashboardController;
use App\Http\Controllers\Register\RegisterController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Tutor\TutorDashboardController;
use App\Http\Controllers\Admin\UserController;
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
Route::view('/account-disabled', 'shared.account-disabled')->middleware('auth')->name('account.disabled');
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Admin
Route::prefix('admin')->middleware(['auth', 'active', 'role:1'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::patch('/users/{user}/toggle-state', [UserController::class, 'toggleState'])->name('users.toggle-state');
    Route::resource('/users', UserController::class);
});

// Moderator
Route::prefix('moderator')->middleware(['auth', 'active', 'role:2'])->group(function () {
    Route::get('/dashboard', [ModeratorDashboardController::class, 'index'])->name('moderator.dashboard');
});

// Tutor
Route::prefix('tutor')->middleware(['auth', 'active', 'role:3'])->group(function () {
    Route::get('/dashboard', [TutorDashboardController::class, 'index'])->name('tutor.dashboard');
});

// Student
Route::prefix('student')->middleware(['auth', 'active', 'role:4'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
});
