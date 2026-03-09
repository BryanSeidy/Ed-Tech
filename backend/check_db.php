<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User, App\Models\Course, App\Models\Module, App\Models\Lesson, App\Models\Quiz, App\Models\Question, App\Models\Answer, App\Models\Progress, App\Models\Attempt;

echo "Database Status:\n";
echo "Users: " . User::count() . "\n";
echo "Courses: " . Course::count() . "\n";
echo "Modules: " . Module::count() . "\n";
echo "Lessons: " . Lesson::count() . "\n";
echo "Quizzes: " . Quiz::count() . "\n";
echo "Questions: " . Question::count() . "\n";
echo "Answers: " . Answer::count() . "\n";
echo "Progress: " . Progress::count() . "\n";
echo "Attempts: " . Attempt::count() . "\n";
