<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Course\EnrollmentController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\Quiz\AttemptController;
use App\Http\Controllers\Quiz\QuizController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::get('/quizzes/{id}', [QuizController::class, 'show']);

Route::middleware('auth')->group(function (): void {
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::post('/courses/{courseId}/enroll', [EnrollmentController::class, 'store']);

    Route::post('/quizzes/{quizId}/attempts', [AttemptController::class, 'store']);
    Route::get('/quiz-attempts', [AttemptController::class, 'index']);

    Route::post('/lessons/{lessonId}/progress', [ProgressController::class, 'markLessonCompleted']);
    Route::get('/courses/{courseId}/progress', [ProgressController::class, 'showCourseProgress']);

    Route::post('/courses/{courseId}/certificate', [CertificateController::class, 'issue']);
});
