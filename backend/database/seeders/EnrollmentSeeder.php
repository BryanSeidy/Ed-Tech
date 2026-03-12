<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        // Alice enrolled in PHP, Laravel, and JavaScript
        Enrollment::create(['user_id' => 4, 'course_id' => 1]);
        Enrollment::create(['user_id' => 4, 'course_id' => 2]);
        Enrollment::create(['user_id' => 4, 'course_id' => 3]);

        // Bob enrolled in Laravel, React, and SQL
        Enrollment::create(['user_id' => 5, 'course_id' => 2]);
        Enrollment::create(['user_id' => 5, 'course_id' => 4]);
        Enrollment::create(['user_id' => 5, 'course_id' => 5]);

        // Carol enrolled in JavaScript and React
        Enrollment::create(['user_id' => 6, 'course_id' => 3]);
        Enrollment::create(['user_id' => 6, 'course_id' => 4]);

        // David enrolled in SQL and PHP
        Enrollment::create(['user_id' => 7, 'course_id' => 1]);
        Enrollment::create(['user_id' => 7, 'course_id' => 5]);

        // Emma enrolled in all published courses
        Enrollment::create(['user_id' => 8, 'course_id' => 1]);
        Enrollment::create(['user_id' => 8, 'course_id' => 2]);
        Enrollment::create(['user_id' => 8, 'course_id' => 3]);
        Enrollment::create(['user_id' => 8, 'course_id' => 4]);
        Enrollment::create(['user_id' => 8, 'course_id' => 5]);
    }
}
