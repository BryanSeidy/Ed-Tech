<?php

namespace Database\Seeders;

use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        // Quiz after Lesson 3 (PHP Basics - Chaînes et Arrays)
        Quiz::create([
            'lesson_id' => 3,
            'title' => 'Quiz Fondamentaux PHP',
            'passing_score' => 60,
        ]);

        // Quiz after Lesson 6 (PHP - Contrôle de flux - Boucles)
        Quiz::create([
            'lesson_id' => 6,
            'title' => 'Quiz Contrôle de Flux',
            'passing_score' => 60,
        ]);

        // Quiz after Lesson 8 (PHP - POO - Objets)
        Quiz::create([
            'lesson_id' => 8,
            'title' => 'Quiz POO en PHP',
            'passing_score' => 65,
        ]);

        // Quiz after Lesson 10 (Laravel - Structure)
        Quiz::create([
            'lesson_id' => 10,
            'title' => 'Quiz Laravel Configuration',
            'passing_score' => 60,
        ]);

        // Quiz after Lesson 12 (Laravel - Eloquent - Relations)
        Quiz::create([
            'lesson_id' => 12,
            'title' => 'Quiz Eloquent ORM',
            'passing_score' => 65,
        ]);

        // Quiz after Lesson 15 (JavaScript - Fonctions)
        Quiz::create([
            'lesson_id' => 15,
            'title' => 'Quiz JavaScript Avancé',
            'passing_score' => 60,
        ]);

        // Quiz after Lesson 17 (React - Composants Fonctionnels)
        Quiz::create([
            'lesson_id' => 17,
            'title' => 'Quiz Composants React',
            'passing_score' => 60,
        ]);

        // Quiz after Lesson 19 (SQL - INSERT, UPDATE, DELETE)
        Quiz::create([
            'lesson_id' => 19,
            'title' => 'Quiz Requêtes SQL',
            'passing_score' => 60,
        ]);
    }
}
