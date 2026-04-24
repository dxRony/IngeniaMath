<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Forum\ForumController;
use App\Http\Controllers\Forum\QuestionController;
use App\Http\Controllers\Forum\QuestionReplyController;
use App\Http\Controllers\Forum\ThreadController;
use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Moderator\ModeratorDashboardController;
use App\Http\Controllers\Moderator\ModeratorExerciseReviewController;
use App\Http\Controllers\Moderator\Resources\ResourceReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Register\RegisterController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Tutor\Resources\ResourceController;
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

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');
Route::view('/account-disabled', 'shared.account-disabled')->middleware('auth')->name('account.disabled');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profiles/{user}', [ProfileController::class, 'showPublic'])->name('profile.public.show');
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

    // Foro
    Route::resource('/forum', ForumController::class)->only(['index'])->name('index', 'moderator.forum.index');
    Route::resource('/threads', ThreadController::class)->only(['show'])->name('show', 'moderator.threads.show');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('moderator.questions.destroy');
    Route::delete('/question-replies/{reply}', [QuestionReplyController::class, 'destroy'])->name('moderator.question-replies.destroy');
    Route::patch('/threads/{thread}/close', [ThreadController::class, 'closeThread'])->name('moderator.threads.close');

    // Revisión de ejercicios
    Route::get('/exercises/review', [ModeratorExerciseReviewController::class, 'index'])->name('moderator.exercises.review.index');
    Route::get('/exercises/review/{exercise}', [ModeratorExerciseReviewController::class, 'show'])->name('moderator.exercises.review.show');
    Route::post('/exercises/review/{exercise}', [ModeratorExerciseReviewController::class, 'review'])->name('moderator.exercises.review.store');
    Route::get('/duplicate-alerts/{duplicateAlert}', [ModeratorExerciseReviewController::class, 'showDuplicate'])->name('moderator.duplicate-alerts.show');
    Route::patch('/duplicate-alerts/{duplicateAlert}/resolve', [ModeratorExerciseReviewController::class, 'resolve'])->name('moderator.duplicate-alerts.resolve');
    Route::patch('/duplicate-alerts/{duplicateAlert}/reject-new-exercise', [ModeratorExerciseReviewController::class, 'rejectNewExercise'])->name('moderator.duplicate-alerts.reject-new-exercise');

    // Revisión de recursos educativos
    Route::prefix('resources')->name('moderator.resources.')->group(function () {
        Route::get('/',              [ResourceReviewController::class, 'index'])->name('index');
        Route::get('/{id}',          [ResourceReviewController::class, 'show'])->name('show')->whereNumber('id');
        Route::post('/{id}/approve', [ResourceReviewController::class, 'approve'])->name('approve')->whereNumber('id');
        Route::post('/{id}/reject',  [ResourceReviewController::class, 'reject'])->name('reject')->whereNumber('id');
    });
});

// Tutor
Route::prefix('tutor')->middleware(['auth', 'active', 'role:3'])->group(function () {
    Route::get('/dashboard', [TutorDashboardController::class, 'index'])->name('tutor.dashboard');

    // Foro
    Route::resource('/forum', ForumController::class)->only(['index'])->name('index', 'tutor.forum.index');
    Route::resource('/threads', ThreadController::class)->only(['show'])->name('show', 'tutor.threads.show');
    Route::post('/question-replies', [QuestionReplyController::class, 'store'])->name('tutor.question-replies.store');

    // Ejercicios
    Route::get('/exercises', [TutorExerciseController::class, 'index'])->name('tutor.exercises.index');
    Route::get('/exercises/create', [TutorExerciseController::class, 'create'])->name('tutor.exercises.create');
    Route::post('/exercises', [TutorExerciseController::class, 'store'])->name('tutor.exercises.store');
    Route::patch('/exercises/{exercise}/submit-review', [TutorExerciseController::class, 'submitReview'])->name('tutor.exercises.submitReview');
    Route::get('/exercises/{exercise}/edit', [TutorExerciseController::class, 'edit'])->name('tutor.exercises.edit');
    Route::put('/exercises/{exercise}', [TutorExerciseController::class, 'update'])->name('tutor.exercises.update');
    Route::get('/exercises/{exercise}', [TutorExerciseController::class, 'show'])->name('tutor.exercises.show');
    Route::get('/exercises/{exercise}/reviews', [TutorExerciseController::class, 'reviews'])->name('tutor.exercises.reviews');

    // Recursos educativos
    Route::prefix('resources')->name('tutor.resources.')->group(function () {
        Route::get('/',                [ResourceController::class, 'index'])->name('index');
        Route::get('/create',          [ResourceController::class, 'create'])->name('create');
        Route::post('/',               [ResourceController::class, 'store'])->name('store');

        // Edición
        Route::get('/{id}/edit',       [ResourceController::class, 'edit'])->name('edit')->whereNumber('id');
        Route::put('/{id}',            [ResourceController::class, 'update'])->name('update')->whereNumber('id');

        // Eliminación (cambia a status 4)
        Route::delete('/{id}',         [ResourceController::class, 'destroy'])->name('destroy')->whereNumber('id');
    });
});

// Student
Route::prefix('student')->middleware(['auth', 'active', 'role:4'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::resource('/forum', ForumController::class)->only(['index'])->name('index', 'student.forum.index');
    Route::resource('/threads', ThreadController::class)->only(['create', 'store', 'show'])->names([
        'create' => 'student.threads.create',
        'store' => 'student.threads.store',
        'show' => 'student.threads.show',
    ]);
    Route::post('/questions', [QuestionController::class, 'store'])->name('student.questions.store');
    Route::patch('/question-replies/{reply}/accept', [QuestionReplyController::class, 'acceptReply'])->name('student.question-replies.accept');
});
