<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Moderator\ModeratorDashboardController;
use App\Http\Controllers\Moderator\ModeratorExerciseReviewController;
use App\Http\Controllers\Register\RegisterController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Tutor\TutorDashboardController;
use App\Http\Controllers\Tutor\TutorExerciseController;
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
    Route::get('/exercises/review', [ModeratorExerciseReviewController::class, 'index'])->name('moderator.exercises.review.index');
    Route::get('/exercises/review/{exercise}', [ModeratorExerciseReviewController::class, 'show'])->name('moderator.exercises.review.show');
    Route::post('/exercises/review/{exercise}', [ModeratorExerciseReviewController::class, 'review'])->name('moderator.exercises.review.store');
});

// Tutor
Route::prefix('tutor')->middleware(['auth', 'role:3'])->group(function () {
    Route::get('/dashboard', [TutorDashboardController::class, 'index'])->name('tutor.dashboard');
    Route::get('/exercises', [TutorExerciseController::class, 'index'])->name('tutor.exercises.index');
    Route::get('/exercises/create', [TutorExerciseController::class, 'create'])->name('tutor.exercises.create');
    Route::post('/exercises', [TutorExerciseController::class, 'store'])->name('tutor.exercises.store');
    Route::patch('/exercises/{exercise}/submit-review', [TutorExerciseController::class, 'submitReview'])->name('tutor.exercises.submitReview');
    Route::get('/exercises/{exercise}/edit', [TutorExerciseController::class, 'edit'])->name('tutor.exercises.edit');
    Route::put('/exercises/{exercise}', [TutorExerciseController::class, 'update'])->name('tutor.exercises.update');
    Route::get('/exercises/{exercise}', [TutorExerciseController::class, 'show'])->name('tutor.exercises.show');
    Route::get('/exercises/{exercise}/reviews', [TutorExerciseController::class, 'reviews'])->name('tutor.exercises.reviews');
});

// Student
Route::prefix('student')->middleware(['auth', 'role:4'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
});
