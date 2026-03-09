<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {

        Course::create([
            'title' => 'Introduction à Laravel',
            'description' => 'Cours complet sur Laravel',
            'teacher_id' => 2
        ]);

        Course::create([
            'title' => 'Développement Web avec React',
            'description' => 'Apprendre React et Next.js',
            'teacher_id' => 2
        ]);

    }
}