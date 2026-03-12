<?php

namespace Database\Seeders;

use App\Models\Attempt;
use Illuminate\Database\Seeder;

class AttemptSeeder extends Seeder
{
    public function run(): void
    {
        // Alice's attempts
        Attempt::create(['user_id' => 4, 'quiz_id' => 1, 'score' => 80, 'attempted_at' => now()->subDays(2)]);
        Attempt::create(['user_id' => 4, 'quiz_id' => 1, 'score' => 90, 'attempted_at' => now()->subDays(1)]);
        Attempt::create(['user_id' => 4, 'quiz_id' => 3, 'score' => 75, 'attempted_at' => now()->subDays(3)]);

        // Bob's attempts
        Attempt::create(['user_id' => 5, 'quiz_id' => 3, 'score' => 85, 'attempted_at' => now()->subDays(4)]);
        Attempt::create(['user_id' => 5, 'quiz_id' => 4, 'score' => 88, 'attempted_at' => now()->subDays(2)]);
        Attempt::create(['user_id' => 5, 'quiz_id' => 7, 'score' => 92, 'attempted_at' => now()->subDays(1)]);

        // Carol's attempts
        Attempt::create(['user_id' => 6, 'quiz_id' => 5, 'score' => 78, 'attempted_at' => now()->subDays(5)]);
        Attempt::create(['user_id' => 6, 'quiz_id' => 6, 'score' => 82, 'attempted_at' => now()->subDays(3)]);

        // David's attempts
        Attempt::create(['user_id' => 7, 'quiz_id' => 1, 'score' => 95, 'attempted_at' => now()->subDays(6)]);
        Attempt::create(['user_id' => 7, 'quiz_id' => 7, 'score' => 89, 'attempted_at' => now()->subDays(2)]);

        // Emma's attempts (Très active)
        Attempt::create(['user_id' => 8, 'quiz_id' => 1, 'score' => 92, 'attempted_at' => now()->subDays(7)]);
        Attempt::create(['user_id' => 8, 'quiz_id' => 2, 'score' => 88, 'attempted_at' => now()->subDays(6)]);
        Attempt::create(['user_id' => 8, 'quiz_id' => 3, 'score' => 90, 'attempted_at' => now()->subDays(5)]);
        Attempt::create(['user_id' => 8, 'quiz_id' => 4, 'score' => 85, 'attempted_at' => now()->subDays(4)]);
        Attempt::create(['user_id' => 8, 'quiz_id' => 5, 'score' => 87, 'attempted_at' => now()->subDays(3)]);
        Attempt::create(['user_id' => 8, 'quiz_id' => 6, 'score' => 91, 'attempted_at' => now()->subDays(2)]);
        Attempt::create(['user_id' => 8, 'quiz_id' => 7, 'score' => 94, 'attempted_at' => now()->subDays(1)]);
    }
}
