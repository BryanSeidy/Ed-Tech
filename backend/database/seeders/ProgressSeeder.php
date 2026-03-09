<?php

namespace Database\Seeders;

use App\Models\Progress;
use Illuminate\Database\Seeder;

class ProgressSeeder extends Seeder
{
    public function run(): void
    {
        // Alice's progress (user_id = 4)
        Progress::create(['user_id' => 4, 'lesson_id' => 1, 'completed' => true, 'completed_at' => now()->subDays(2)]);
        Progress::create(['user_id' => 4, 'lesson_id' => 2, 'completed' => true, 'completed_at' => now()->subDays(1)]);
        Progress::create(['user_id' => 4, 'lesson_id' => 3, 'completed' => false]);

        // Bob's progress (user_id = 5)
        Progress::create(['user_id' => 5, 'lesson_id' => 9, 'completed' => true, 'completed_at' => now()->subDays(3)]);
        Progress::create(['user_id' => 5, 'lesson_id' => 10, 'completed' => true, 'completed_at' => now()->subDays(2)]);
        Progress::create(['user_id' => 5, 'lesson_id' => 11, 'completed' => true, 'completed_at' => now()->subDays(1)]);

        // Carol's progress (user_id = 6)
        Progress::create(['user_id' => 6, 'lesson_id' => 14, 'completed' => true, 'completed_at' => now()->subDays(4)]);
        Progress::create(['user_id' => 6, 'lesson_id' => 15, 'completed' => false]);

        // David's progress (user_id = 7)
        Progress::create(['user_id' => 7, 'lesson_id' => 1, 'completed' => true, 'completed_at' => now()->subDays(5)]);
        Progress::create(['user_id' => 7, 'lesson_id' => 18, 'completed' => true, 'completed_at' => now()->subDays(3)]);
        Progress::create(['user_id' => 7, 'lesson_id' => 19, 'completed' => true, 'completed_at' => now()->subDays(1)]);

        // Emma's progress (user_id = 8)
        Progress::create(['user_id' => 8, 'lesson_id' => 1, 'completed' => true, 'completed_at' => now()->subDays(6)]);
        Progress::create(['user_id' => 8, 'lesson_id' => 2, 'completed' => true, 'completed_at' => now()->subDays(5)]);
        Progress::create(['user_id' => 8, 'lesson_id' => 3, 'completed' => true, 'completed_at' => now()->subDays(4)]);
        Progress::create(['user_id' => 8, 'lesson_id' => 4, 'completed' => true, 'completed_at' => now()->subDays(3)]);
        Progress::create(['user_id' => 8, 'lesson_id' => 9, 'completed' => true, 'completed_at' => now()->subDays(2)]);
        Progress::create(['user_id' => 8, 'lesson_id' => 10, 'completed' => true, 'completed_at' => now()->subDays(1)]);
    }
}
