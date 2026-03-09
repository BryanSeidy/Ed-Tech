<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Course 1 - PHP Basics
        Course::create([
            'title' => 'Maîtriser PHP pour le web',
            'description' => 'Apprenez les bases de PHP et créez des applications web dynamiques. Ce cours couvre les concepts fondamentaux, la programmation orientée objet et l\'intégration avec les bases de données.',
            'instructor_id' => 1,
            'is_published' => true,
            'thumbnail' => null,
        ]);

        // Course 2 - Laravel Framework
        Course::create([
            'title' => 'Développement avec Laravel',
            'description' => 'Maîtrisez le framework Laravel pour construire des applications web modernes et scalables. Incluant Eloquent ORM, Migrations, et API REST.',
            'instructor_id' => 1,
            'is_published' => true,
            'thumbnail' => null,
        ]);

        // Course 3 - JavaScript
        Course::create([
            'title' => 'JavaScript Avancé',
            'description' => 'De débutant à expert en JavaScript. Couvre ES6+, Async/Await, Promises, et les patrons de conception.',
            'instructor_id' => 2,
            'is_published' => true,
            'thumbnail' => null,
        ]);

        // Course 4 - React Basics
        Course::create([
            'title' => 'Introduction à React',
            'description' => 'Apprenez les fondamentaux de React pour construire des interfaces utilisateur interactives et réutilisables.',
            'instructor_id' => 2,
            'is_published' => true,
            'thumbnail' => null,
        ]);

        // Course 5 - Database & SQL
        Course::create([
            'title' => 'Maîtriser les Bases de Données SQL',
            'description' => 'Conception et optimisation de bases de données relationnelles. Requêtes complexes, normalisation et performance.',
            'instructor_id' => 3,
            'is_published' => true,
            'thumbnail' => null,
        ]);

        // Course 6 - Unpublished Course
        Course::create([
            'title' => 'API REST Avancées',
            'description' => 'Construction d\'APIs REST sécurisées et performantes avec authentification et gestion des erreurs.',
            'instructor_id' => 3,
            'is_published' => false,
            'thumbnail' => null,
        ]);
    }
}
