<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

public function up(): void
{
Schema::create('questions', function (Blueprint $table) {
$table->id();
$table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
$table->text('question_text');
$table->string('type');
$table->integer('position')->default(0);
});
}

public function down(): void
{
Schema::dropIfExists('questions');
}

};
