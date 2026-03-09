<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Questions for Quiz 1
        Question::create([
            'quiz_id' => 1,
            'question_text' => 'Quelle est la syntaxe correcte pour déclarer une variable en PHP?',
            'type' => 'multiple_choice',
        ]);

        Question::create([
            'quiz_id' => 1,
            'question_text' => 'Comment inclure un fichier externe en PHP?',
            'type' => 'multiple_choice',
        ]);

        Question::create([
            'quiz_id' => 1,
            'question_text' => 'Quel est le type de variable pour un nombre décimal?',
            'type' => 'multiple_choice',
        ]);

        // Questions for Quiz 2
        Question::create([
            'quiz_id' => 2,
            'question_text' => 'Qu\'est-ce que l\'encapsulation en POO?',
            'type' => 'multiple_choice',
        ]);

        Question::create([
            'quiz_id' => 2,
            'question_text' => 'Quel mot-clé crée une instance de classe?',
            'type' => 'multiple_choice',
        ]);

        // Questions for Quiz 3
        Question::create([
            'quiz_id' => 3,
            'question_text' => 'Quel gestionnaire de paquets est utilisé pour Laravel?',
            'type' => 'multiple_choice',
        ]);

        Question::create([
            'quiz_id' => 3,
            'question_text' => 'Quelle est la commande pour créer un contrôleur Laravel?',
            'type' => 'multiple_choice',
        ]);

        // Questions for Quiz 4
        Question::create([
            'quiz_id' => 4,
            'question_text' => 'Comment définir une relation One-to-Many en Eloquent?',
            'type' => 'multiple_choice',
        ]);

        Question::create([
            'quiz_id' => 4,
            'question_text' => 'Quel est le nom de la méthode pour récupérer tous les enregistrements?',
            'type' => 'multiple_choice',
        ]);

        // Questions for Quiz 5
        Question::create([
            'quiz_id' => 5,
            'question_text' => 'Quelle est la différence entre let et const?',
            'type' => 'multiple_choice',
        ]);

        Question::create([
            'quiz_id' => 5,
            'question_text' => 'Comment utiliser une arrow function?',
            'type' => 'multiple_choice',
        ]);

        // Questions for Quiz 6
        Question::create([
            'quiz_id' => 6,
            'question_text' => 'Qu\'est-ce qu\'un composant React?',
            'type' => 'multiple_choice',
        ]);

        Question::create([
            'quiz_id' => 6,
            'question_text' => 'Quel hook gère l\'état en React?',
            'type' => 'multiple_choice',
        ]);

        // Questions for Quiz 7
        Question::create([
            'quiz_id' => 7,
            'question_text' => 'Quelle clause filtre les lignes retournées par SELECT?',
            'type' => 'multiple_choice',
        ]);

        Question::create([
            'quiz_id' => 7,
            'question_text' => 'Comment trier les résultats en SQL?',
            'type' => 'multiple_choice',
        ]);

        // Questions for Quiz 8
        Question::create([
            'quiz_id' => 8,
            'question_text' => 'Quel type de jointure combine les enregistrements correspondants?',
            'type' => 'multiple_choice',
        ]);

        Question::create([
            'quiz_id' => 8,
            'question_text' => 'Comment compter le nombre de lignes?',
            'type' => 'multiple_choice',
        ]);
    }
}
