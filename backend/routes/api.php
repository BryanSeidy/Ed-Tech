<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Course\EnrollmentController;
use App\Http\Controllers\Course\LessonController;
use App\Http\Controllers\Course\ModuleController;
use App\Http\Controllers\Live\LiveSessionController;
use App\Http\Controllers\InstructorDashboardController;
use App\Http\Controllers\Live\VideoConferenceController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\Quiz\AnswersController;
use App\Http\Controllers\Quiz\AttemptController;
use App\Http\Controllers\Quiz\QuizController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');


    Route::middleware(['auth:sanctum'])->group(function (): void {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::get('certificates/verify/{certificateNumber}', [CertificateController::class, 'verifyPublic'])->middleware('throttle:30,1');

Route::middleware(['auth:sanctum'])->group(function (): void {
    // Authenticated user dashboard and profile.
    Route::get('dashboard', [UserController::class, 'dashboard']);
    Route::get('profile', [UserController::class, 'profile']);
    Route::put('profile', [UserController::class, 'updateProfile']);
    Route::post('profile/change-password', [UserController::class, 'changePassword']);
    Route::get('user/profile', [UserController::class, 'profile']);

    Route::get('my-courses', [UserController::class, 'myCourses']);
    Route::get('my-enrollments', [UserController::class, 'myEnrollments']);
    Route::get('my-certificates', [UserController::class, 'myCertificates']);
    Route::get('my-statistics', [UserController::class, 'myStatistics']);

    // Instructor dashboard metrics and resources.
    Route::get('instructor/stats', [InstructorDashboardController::class, 'stats']);
    Route::get('instructor/courses', [InstructorDashboardController::class, 'courses']);
    Route::get('instructor/live-sessions', [InstructorDashboardController::class, 'liveSessions']);

    // Course catalogue, instructor course management, and learner enrollment.
    Route::get('courses', [CourseController::class, 'index']);
    Route::post('courses', [CourseController::class, 'store']);
    Route::get('courses/my/enrolled', [CourseController::class, 'myCourses']);
    Route::get('courses/my/created', [CourseController::class, 'myCreatedCourses']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
    Route::put('courses/{course}', [CourseController::class, 'update']);
    Route::delete('courses/{course}', [CourseController::class, 'destroy']);
    Route::patch('courses/{course}/publish', [CourseController::class, 'publish']);
    Route::patch('courses/{course}/unpublish', [CourseController::class, 'unpublish']);
    Route::get('courses/{course}/statistics', [CourseController::class, 'statistics']);
    Route::post('courses/{course}/enroll', [EnrollmentController::class, 'store']);
    Route::delete('courses/{course}/enroll', [CourseController::class, 'unenroll']);
    Route::get('courses/{course}/enrollment/check', [EnrollmentController::class, 'checkEnrollment']);
    Route::get('courses/{course}/live-sessions', [LiveSessionController::class, 'indexForCourse']);

    // Live sessions and classroom chat.
    Route::get('live-sessions/upcoming', [LiveSessionController::class, 'upcoming']);
    Route::post('live-sessions', [LiveSessionController::class, 'store']);
    Route::get('live-sessions/{liveSession}/join', [LiveSessionController::class, 'join']);
    Route::get('live-sessions/{liveSession}/messages', [LiveSessionController::class, 'messages']);
    Route::post('live-sessions/{liveSession}/messages', [LiveSessionController::class, 'storeMessage']);

    // Course modules.
    Route::get('courses/{course}/modules', [ModuleController::class, 'index']);
    Route::post('courses/{course}/modules', [ModuleController::class, 'store']);
    Route::patch('courses/{course}/modules/reorder', [ModuleController::class, 'reorder']);
    Route::get('modules/{module}', [ModuleController::class, 'show']);
    Route::put('modules/{module}', [ModuleController::class, 'update']);
    Route::delete('modules/{module}', [ModuleController::class, 'destroy']);
    Route::get('modules/{module}/statistics', [ModuleController::class, 'statistics']);
    Route::get('modules/{module}/progress', [ModuleController::class, 'progress']);
    Route::get('modules/{module}/next', [ModuleController::class, 'nextModule']);
    Route::get('modules/{module}/previous', [ModuleController::class, 'previousModule']);

    // Lessons and learner progress aliases used by the Next.js app.
    Route::get('modules/{module}/lessons', [LessonController::class, 'index']);
    Route::post('modules/{module}/lessons', [LessonController::class, 'store']);
    Route::patch('modules/{module}/lessons/reorder', [LessonController::class, 'reorder']);
    Route::get('lessons/{lesson}', [LessonController::class, 'show']);
    Route::put('lessons/{lesson}', [LessonController::class, 'update']);
    Route::delete('lessons/{lesson}', [LessonController::class, 'destroy']);
    Route::post('lessons/{lesson}/complete', [LessonController::class, 'markCompleted']);
    Route::delete('lessons/{lesson}/complete', [LessonController::class, 'markIncomplete']);
    Route::get('lessons/{lesson}/progress', [LessonController::class, 'getProgress']);
    Route::post('lessons/{lessonId}/progress', [ProgressController::class, 'markLessonCompleted']);
    Route::get('lessons/{lesson}/next', [LessonController::class, 'nextLesson']);
    Route::get('lessons/{lesson}/previous', [LessonController::class, 'previousLesson']);
    Route::post('lessons/{lessonId}/live-room', [VideoConferenceController::class, 'createRoom']);

    // Enrollments.
    Route::get('enrollments/my', [EnrollmentController::class, 'myEnrollments']);
    Route::get('enrollments/{enrollment}', [EnrollmentController::class, 'show']);
    Route::delete('enrollments/{enrollment}', [EnrollmentController::class, 'destroy']);
    Route::get('courses/{course}/enrollments', [EnrollmentController::class, 'index']);
    Route::get('courses/{course}/enrollments/statistics', [EnrollmentController::class, 'statistics']);
    Route::get('courses/{course}/enrollments/export', [EnrollmentController::class, 'export']);
    Route::post('courses/{course}/enrollments/bulk', [EnrollmentController::class, 'bulkEnroll']);
    Route::delete('courses/{course}/enrollments/bulk', [EnrollmentController::class, 'bulkUnenroll']);

    // Quiz management and attempts.
    Route::get('quizzes', [QuizController::class, 'index']);
    Route::post('quizzes', [QuizController::class, 'store']);
    Route::get('my-quizzes', [QuizController::class, 'myQuizzes']);
    Route::get('enrolled-quizzes', [QuizController::class, 'enrolledQuizzes']);
    Route::get('quizzes/{quiz}', [QuizController::class, 'show']);
    Route::put('quizzes/{quiz}', [QuizController::class, 'update']);
    Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy']);
    Route::post('quizzes/{quiz}/publish', [QuizController::class, 'publish']);
    Route::post('quizzes/{quiz}/unpublish', [QuizController::class, 'unpublish']);
    Route::get('quizzes/{quiz}/questions', [QuizController::class, 'getQuestions']);
    Route::get('quizzes/{quiz}/statistics', [QuizController::class, 'statistics']);
    Route::post('quizzes/{quiz}/reorder-questions', [QuizController::class, 'reorderQuestions']);
    Route::post('quizzes/{quiz}/start-attempt', [QuizController::class, 'startAttempt']);
    Route::post('quizzes/{quiz}/submit', [QuizController::class, 'submitAnswers']);
    Route::get('quizzes/{quiz}/results/{attempt}', [QuizController::class, 'getResults']);
    Route::get('quizzes/{quiz}/my-attempts', [AttemptController::class, 'userAttempts']);
    Route::get('quizzes/{quiz}/attempts', [AttemptController::class, 'index']);
    Route::get('quizzes/{quiz}/attempts/statistics', [AttemptController::class, 'statistics']);
    Route::post('quizzes/{quiz}/attempts', [AttemptController::class, 'store']);
    Route::get('attempts/my', [AttemptController::class, 'myAttempts']);
    Route::get('quiz-attempts', [AttemptController::class, 'myAttempts']);
    Route::get('attempts/{attempt}', [AttemptController::class, 'show']);
    Route::put('attempts/{attempt}', [AttemptController::class, 'update']);
    Route::delete('attempts/{attempt}', [AttemptController::class, 'destroy']);
    Route::get('attempts/{attempt}/results', [AttemptController::class, 'results']);

    // Question answers.
    Route::get('questions/{question}/answers', [AnswersController::class, 'index']);
    Route::post('questions/{question}/answers', [AnswersController::class, 'store']);
    Route::post('questions/{question}/answers/bulk', [AnswersController::class, 'bulkStore']);
    Route::patch('questions/{question}/answers/reorder', [AnswersController::class, 'reorder']);
    Route::get('questions/{question}/correct-answer', [AnswersController::class, 'correctAnswer']);
    Route::get('answers/{answer}', [AnswersController::class, 'show']);
    Route::put('answers/{answer}', [AnswersController::class, 'update']);
    Route::delete('answers/{answer}', [AnswersController::class, 'destroy']);
    Route::patch('answers/{answer}/correct', [AnswersController::class, 'setCorrect']);
    Route::delete('answers/{answer}/correct', [AnswersController::class, 'unsetCorrect']);

    // Certificates.
    Route::get('certificates', [CertificateController::class, 'index']);
    Route::post('certificates', [CertificateController::class, 'store']);
    Route::post('certificates/generate', [CertificateController::class, 'generate']);
    Route::post('certificates/verify', [CertificateController::class, 'verify']);
    Route::get('certificates/eligibility', [CertificateController::class, 'checkEligibility']);
    Route::get('certificates/{certificate}', [CertificateController::class, 'show']);
    Route::put('certificates/{certificate}', [CertificateController::class, 'update']);
    Route::delete('certificates/{certificate}', [CertificateController::class, 'destroy']);
    Route::get('certificates/{certificate}/download', [CertificateController::class, 'download']);

    // Progress.
    Route::get('my-progress', [ProgressController::class, 'myProgress']);
    Route::get('progress', [ProgressController::class, 'index']);
    Route::post('progress', [ProgressController::class, 'store']);
    Route::post('progress/mark-completed', [ProgressController::class, 'markCompleted']);
    Route::post('progress/mark-incomplete', [ProgressController::class, 'markIncomplete']);
    Route::post('progress/bulk-update', [ProgressController::class, 'bulkUpdate']);
    Route::get('progress/user/{user}', [ProgressController::class, 'getUserProgress']);
    Route::get('progress/{progress}', [ProgressController::class, 'show']);
    Route::put('progress/{progress}', [ProgressController::class, 'update']);
    Route::delete('progress/{progress}', [ProgressController::class, 'destroy']);
    Route::get('courses/{courseId}/progress', [ProgressController::class, 'showCourseProgress']);
    Route::post('courses/{course}/progress/reset', [ProgressController::class, 'resetCourseProgress']);

    // User administration.
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
    Route::get('users/{user}/courses', [UserController::class, 'getUserCourses']);
    Route::get('users/{user}/enrollments', [UserController::class, 'getUserEnrollments']);
    Route::get('users/{user}/progress', [UserController::class, 'getUserProgress']);
    Route::get('users/{user}/certificates', [UserController::class, 'getUserCertificates']);
    Route::get('users/{user}/statistics', [UserController::class, 'getUserStatistics']);
});
