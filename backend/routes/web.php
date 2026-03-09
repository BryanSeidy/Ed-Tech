<?php


use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Course\EnrollmentController;
use App\Http\Controllers\Course\LessonController;
use App\Http\Controllers\Course\ModuleController;
use App\Http\Controllers\Quiz\AnswersController;
use App\Http\Controllers\Quiz\AttemptController;
use App\Http\Controllers\Quiz\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
    Route::post('modules/{module}/lessons', [LessonController::class, 'store']);
