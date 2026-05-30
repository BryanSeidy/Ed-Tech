<?php

use App\Models\Attempt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table): void {
            $table->boolean('passed')->default(false)->after('score');
            $table->timestamp('submitted_at')->nullable()->after('attempted_at');
        });

        Attempt::query()
            ->with('quiz:id,passing_score')
            ->whereNull('submitted_at')
            ->get()
            ->each(function (Attempt $attempt): void {
                $attempt->forceFill([
                    'passed' => $attempt->quiz ? $attempt->score >= $attempt->quiz->passing_score : false,
                    'submitted_at' => $attempt->attempted_at,
                ])->save();
            });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table): void {
            $table->dropColumn(['passed', 'submitted_at']);
        });
    }
};
