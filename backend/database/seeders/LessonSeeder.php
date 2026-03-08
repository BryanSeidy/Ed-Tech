<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;

class LessonSeeder extends Seeder
{
    public function run(): void
    {

        Lesson::create([
            'module_id' => 1,
            'title' => 'Présentation du cours',
            'content' => 'Bienvenue dans ce cours Laravel',
            'video_url' => null,
            'pdf_file' => null
        ]);

        Lesson::create([
            'module_id' => 2,
            'title' => 'Installer Laravel',
            'content' => 'Guide d installation Laravel',
            'video_url' => null,
            'pdf_file' => null
        ]);

    }
}