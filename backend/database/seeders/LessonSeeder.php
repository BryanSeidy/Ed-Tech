<?php

namespace Database\Seeders;

use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        // Lessons for Module 1 (PHP Basics - Fondamentaux)
        Lesson::create([
            'module_id' => 1,
            'title' => 'Variables et Types de Données',
            'content' => 'Comprenez comment déclarer et utiliser des variables en PHP, et les différents types de données disponibles.',
            'video_url' => null,
            'duration' => 15,
            'position' => 1,
        ]);

        Lesson::create([
            'module_id' => 1,
            'title' => 'Opérateurs et Expressions',
            'content' => 'Explorez les opérateurs arithmétiques, logiques et de comparaison.',
            'video_url' => null,
            'duration' => 20,
            'position' => 2,
        ]);

        Lesson::create([
            'module_id' => 1,
            'title' => 'Chaînes et Arrays',
            'content' => 'Travaillez avec les chaînes de caractères et les tableaux en PHP.',
            'video_url' => null,
            'duration' => 25,
            'position' => 3,
        ]);

        // Lessons for Module 2 (PHP - Contrôle de flux)
        Lesson::create([
            'module_id' => 2,
            'title' => 'Conditions if/else',
            'content' => 'La syntaxe et l\'utilisation des conditions if, else if, et else.',
            'video_url' => null,
            'duration' => 18,
            'position' => 1,
        ]);

        Lesson::create([
            'module_id' => 2,
            'title' => 'Switch et Match',
            'content' => 'Utiliser switch pour des conditions multiples et match pour les cas complexes.',
            'video_url' => null,
            'duration' => 15,
            'position' => 2,
        ]);

        Lesson::create([
            'module_id' => 2,
            'title' => 'Boucles: for, foreach, while',
            'content' => 'Maîtriser les différentes structures de boucles.',
            'video_url' => null,
            'duration' => 22,
            'position' => 3,
        ]);

        // Lessons for Module 3 (PHP - POO)
        Lesson::create([
            'module_id' => 3,
            'title' => 'Principes de la POO',
            'content' => 'Introduction à la Programmation Orientée Objet.',
            'video_url' => null,
            'duration' => 30,
            'position' => 1,
        ]);

        Lesson::create([
            'module_id' => 3,
            'title' => 'Classes et Objets',
            'content' => 'Créer et utiliser des classes et des objets.',
            'video_url' => null,
            'duration' => 25,
            'position' => 2,
        ]);

        // Lessons for Laravel Module 1
        Lesson::create([
            'module_id' => 4,
            'title' => 'Installation et Configuration',
            'content' => 'Installer Laravel et configurer votre environnement.',
            'video_url' => null,
            'duration' => 20,
            'position' => 1,
        ]);

        Lesson::create([
            'module_id' => 4,
            'title' => 'Structure du Projet',
            'content' => 'Comprendre la structure des répertoires de Laravel.',
            'video_url' => null,
            'duration' => 18,
            'position' => 2,
        ]);

        // Lessons for Laravel Module 2 (Eloquent)
        Lesson::create([
            'module_id' => 5,
            'title' => 'Créer des Modèles',
            'content' => 'Générer et configurer des modèles Eloquent.',
            'video_url' => null,
            'duration' => 20,
            'position' => 1,
        ]);

        Lesson::create([
            'module_id' => 5,
            'title' => 'Requêtes Eloquent',
            'content' => 'Utiliser Eloquent pour récupérer, créer et mettre à jour des données.',
            'video_url' => null,
            'duration' => 25,
            'position' => 2,
        ]);

        Lesson::create([
            'module_id' => 5,
            'title' => 'Relations Entre Modèles',
            'content' => 'Configurer les relations One-to-One, One-to-Many et Many-to-Many.',
            'video_url' => null,
            'duration' => 28,
            'position' => 3,
        ]);

        // Lessons for JavaScript Module 1
        Lesson::create([
            'module_id' => 7,
            'title' => 'Variables et Hoisting',
            'content' => 'Différences entre var, let et const. Comprendre le hoisting.',
            'video_url' => null,
            'duration' => 20,
            'position' => 1,
        ]);

        Lesson::create([
            'module_id' => 7,
            'title' => 'Fonctions et Closures',
            'content' => 'Déclarer des fonctions et comprendre les closures.',
            'video_url' => null,
            'duration' => 25,
            'position' => 2,
        ]);

        // Lessons for React Module 1
        Lesson::create([
            'module_id' => 9,
            'title' => 'Hello React',
            'content' => 'Créer votre première application React.',
            'video_url' => null,
            'duration' => 15,
            'position' => 1,
        ]);

        Lesson::create([
            'module_id' => 9,
            'title' => 'Composants Fonctionnels',
            'content' => 'Créer des composants avec les fonctions JavaScript.',
            'video_url' => null,
            'duration' => 20,
            'position' => 2,
        ]);

        // Lessons for SQL
        Lesson::create([
            'module_id' => 11,
            'title' => 'SELECT - Récupérer les Données',
            'content' => 'Syntaxe de base SELECT et filtrage avec WHERE.',
            'video_url' => null,
            'duration' => 20,
            'position' => 1,
        ]);

        Lesson::create([
            'module_id' => 11,
            'title' => 'INSERT, UPDATE, DELETE',
            'content' => 'Modifier les données dans vos tables.',
            'video_url' => null,
            'duration' => 22,
            'position' => 2,
        ]);
    }
}
