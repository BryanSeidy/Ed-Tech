<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

public function up(): void
{
Schema::create('quizzes', function (Blueprint $table) {
$table->id();
$table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
$table->string('title');
$table->text('description')->nullable();
$table->integer('passing_score')->default(50);
$table->integer('duration_minutes')->nullable();
$table->boolean('is_published')->default(false);
$table->boolean('allow_review')->default(true);
$table->boolean('show_answers')->default(false);
});
}

public function down(): void
{
Schema::dropIfExists('quizzes');
}

};
