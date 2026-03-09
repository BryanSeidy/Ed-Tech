<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$counts = [
    'users' => DB::table('users')->count(),
    'courses' => DB::table('courses')->count(),
    'modules' => DB::table('modules')->count(),
    'lessons' => DB::table('lessons')->count(),
    'quizzes' => DB::table('quizzes')->count(),
    'questions' => DB::table('questions')->count(),
    'answers' => DB::table('answers')->count(),
    'progress' => DB::table('progress')->count(),
    'attempts' => DB::table('attempts')->count(),
];

foreach ($counts as $table => $count) {
    echo "$table: $count\n";
}
