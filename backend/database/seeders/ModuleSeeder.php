<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Modules for Course 1 - PHP Basics
        Module::create([
            'course_id' => 1,
            'title' => 'Fondamentaux PHP',
            'position' => 1,
        ]);

        Module::create([
            'course_id' => 1,
            'title' => 'Contrôle de flux',
            'position' => 2,
        ]);

        Module::create([
            'course_id' => 1,
            'title' => 'Programmation Orientée Objet',
            'position' => 3,
        ]);

        // Modules for Course 2 - Laravel
        Module::create([
            'course_id' => 2,
            'title' => 'Introduction à Laravel',
            'position' => 1,
        ]);

        Module::create([
            'course_id' => 2,
            'title' => 'Eloquent ORM',
            'position' => 2,
        ]);

        Module::create([
            'course_id' => 2,
            'title' => 'Routage et Contrôleurs',
            'position' => 3,
        ]);

        // Modules for Course 3 - JavaScript
        Module::create([
            'course_id' => 3,
            'title' => 'Fondamentaux JavaScript',
            'position' => 1,
        ]);

        Module::create([
            'course_id' => 3,
            'title' => 'ES6 et Syntaxe Moderne',
            'position' => 2,
        ]);

        // Modules for Course 4 - React
        Module::create([
            'course_id' => 4,
            'title' => 'Composants React',
            'position' => 1,
        ]);

        Module::create([
            'course_id' => 4,
            'title' => 'État et Props',
            'position' => 2,
        ]);

        // Modules for Course 5 - SQL
        Module::create([
            'course_id' => 5,
            'title' => 'Requêtes de base',
            'position' => 1,
        ]);

        Module::create([
            'course_id' => 5,
            'title' => 'Jointures et Agrégations',
            'position' => 2,
        ]);
    }
}
