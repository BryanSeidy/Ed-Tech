<?php

namespace Database\Seeders;

use App\Models\Answer;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    public function run(): void
    {
        // Answers for Question 1
        Answer::create(['question_id' => 1, 'answer_text' => '$var = "valeur";', 'is_correct' => true]);
        Answer::create(['question_id' => 1, 'answer_text' => 'var $var = "valeur";', 'is_correct' => false]);
        Answer::create(['question_id' => 1, 'answer_text' => 'variable $var = "valeur";', 'is_correct' => false]);
        Answer::create(['question_id' => 1, 'answer_text' => 'let $var = "valeur";', 'is_correct' => false]);

        // Answers for Question 2
        Answer::create(['question_id' => 2, 'answer_text' => 'include ou require', 'is_correct' => true]);
        Answer::create(['question_id' => 2, 'answer_text' => 'import', 'is_correct' => false]);
        Answer::create(['question_id' => 2, 'answer_text' => 'load', 'is_correct' => false]);
        Answer::create(['question_id' => 2, 'answer_text' => 'attach', 'is_correct' => false]);

        // Answers for Question 3
        Answer::create(['question_id' => 3, 'answer_text' => 'float', 'is_correct' => true]);
        Answer::create(['question_id' => 3, 'answer_text' => 'double', 'is_correct' => false]);
        Answer::create(['question_id' => 3, 'answer_text' => 'decimal', 'is_correct' => false]);
        Answer::create(['question_id' => 3, 'answer_text' => 'numeric', 'is_correct' => false]);

        // Answers for Question 4
        Answer::create(['question_id' => 4, 'answer_text' => '$array[index]', 'is_correct' => true]);
        Answer::create(['question_id' => 4, 'answer_text' => '$array(index)', 'is_correct' => false]);
        Answer::create(['question_id' => 4, 'answer_text' => '$array{index}', 'is_correct' => false]);
        Answer::create(['question_id' => 4, 'answer_text' => '$array->index', 'is_correct' => false]);

        // Answers for Question 5
        Answer::create(['question_id' => 5, 'answer_text' => 'strlen()', 'is_correct' => true]);
        Answer::create(['question_id' => 5, 'answer_text' => 'length()', 'is_correct' => false]);
        Answer::create(['question_id' => 5, 'answer_text' => 'size()', 'is_correct' => false]);
        Answer::create(['question_id' => 5, 'answer_text' => 'count()', 'is_correct' => false]);

        // Answers for Question 6 (OOP)
        Answer::create(['question_id' => 6, 'answer_text' => 'Masquer les détails internes et ne montrer que l\'interface', 'is_correct' => true]);
        Answer::create(['question_id' => 6, 'answer_text' => 'Créer plusieurs classes avec le même code', 'is_correct' => false]);
        Answer::create(['question_id' => 6, 'answer_text' => 'Hériter de plusieurs classes', 'is_correct' => false]);
        Answer::create(['question_id' => 6, 'answer_text' => 'Coder en une seule classe', 'is_correct' => false]);

        // Answers for Question 7
        Answer::create(['question_id' => 7, 'answer_text' => 'parent::', 'is_correct' => true]);
        Answer::create(['question_id' => 7, 'answer_text' => 'super::', 'is_correct' => false]);
        Answer::create(['question_id' => 7, 'answer_text' => 'father::', 'is_correct' => false]);
        Answer::create(['question_id' => 7, 'answer_text' => 'base::', 'is_correct' => false]);

        // Answers for Question 8
        Answer::create(['question_id' => 8, 'answer_text' => 'new', 'is_correct' => true]);
        Answer::create(['question_id' => 8, 'answer_text' => 'create', 'is_correct' => false]);
        Answer::create(['question_id' => 8, 'answer_text' => 'instance', 'is_correct' => false]);
        Answer::create(['question_id' => 8, 'answer_text' => 'make', 'is_correct' => false]);

        // Answers for Question 9 (Laravel)
        Answer::create(['question_id' => 9, 'answer_text' => 'Composer', 'is_correct' => true]);
        Answer::create(['question_id' => 9, 'answer_text' => 'npm', 'is_correct' => false]);
        Answer::create(['question_id' => 9, 'answer_text' => 'pip', 'is_correct' => false]);
        Answer::create(['question_id' => 9, 'answer_text' => 'yarn', 'is_correct' => false]);

        // Answers for Question 10
        Answer::create(['question_id' => 10, 'answer_text' => 'php artisan make:controller NomController', 'is_correct' => true]);
        Answer::create(['question_id' => 10, 'answer_text' => 'php artisan create:controller NomController', 'is_correct' => false]);
        Answer::create(['question_id' => 10, 'answer_text' => 'php make controller NomController', 'is_correct' => false]);
        Answer::create(['question_id' => 10, 'answer_text' => 'artisan controller:make NomController', 'is_correct' => false]);

        // Answers for Question 11 (Eloquent)
        Answer::create(['question_id' => 11, 'answer_text' => 'public function lessons() { return $this->hasMany(Lesson::class); }', 'is_correct' => true]);
        Answer::create(['question_id' => 11, 'answer_text' => 'public function lessons() { return $this->belongsToMany(Lesson::class); }', 'is_correct' => false]);
        Answer::create(['question_id' => 11, 'answer_text' => 'public function lessons() { return $this->hasOne(Lesson::class); }', 'is_correct' => false]);
        Answer::create(['question_id' => 11, 'answer_text' => 'public function lessons() { return $this->hasMany(Lesson::class, foreign_key); }', 'is_correct' => false]);

        // Answers for Question 12
        Answer::create(['question_id' => 12, 'answer_text' => 'Model::all()', 'is_correct' => true]);
        Answer::create(['question_id' => 12, 'answer_text' => 'Model::get()', 'is_correct' => false]);
        Answer::create(['question_id' => 12, 'answer_text' => 'Model::fetch()', 'is_correct' => false]);
        Answer::create(['question_id' => 12, 'answer_text' => 'Model::select()', 'is_correct' => false]);

        // Answers for Question 13 (JavaScript)
        Answer::create(['question_id' => 13, 'answer_text' => 'const ne peut pas être réassigné, let peut', 'is_correct' => true]);
        Answer::create(['question_id' => 13, 'answer_text' => 'Aucune différence', 'is_correct' => false]);
        Answer::create(['question_id' => 13, 'answer_text' => 'let a une portée globale, const locaile', 'is_correct' => false]);
        Answer::create(['question_id' => 13, 'answer_text' => 'const est plus rapide que let', 'is_correct' => false]);

        // Answers for Question 14
        Answer::create(['question_id' => 14, 'answer_text' => '(param) => { return param * 2; }', 'is_correct' => true]);
        Answer::create(['question_id' => 14, 'answer_text' => 'function(param) => { return param * 2; }', 'is_correct' => false]);
        Answer::create(['question_id' => 14, 'answer_text' => 'arrow param => param * 2 { }', 'is_correct' => false]);
        Answer::create(['question_id' => 14, 'answer_text' => '=> (param) param * 2', 'is_correct' => false]);

        // Answers for Question 15 (React)
        Answer::create(['question_id' => 15, 'answer_text' => 'Une fonction ou classe retournant du JSX', 'is_correct' => true]);
        Answer::create(['question_id' => 15, 'answer_text' => 'Un fichier HTML dans React', 'is_correct' => false]);
        Answer::create(['question_id' => 15, 'answer_text' => 'Une variable globale', 'is_correct' => false]);
        Answer::create(['question_id' => 15, 'answer_text' => 'Une méthode CSS', 'is_correct' => false]);

        // Answers for Question 16
        Answer::create(['question_id' => 16, 'answer_text' => 'useState()', 'is_correct' => true]);
        Answer::create(['question_id' => 16, 'answer_text' => 'useEffect()', 'is_correct' => false]);
        Answer::create(['question_id' => 16, 'answer_text' => 'useContext()', 'is_correct' => false]);
        Answer::create(['question_id' => 16, 'answer_text' => 'useReducer()', 'is_correct' => false]);

        // Answers for Question 17 (SQL)
        Answer::create(['question_id' => 17, 'answer_text' => 'WHERE', 'is_correct' => true]);
        Answer::create(['question_id' => 17, 'answer_text' => 'FILTER', 'is_correct' => false]);
        Answer::create(['question_id' => 17, 'answer_text' => 'IF', 'is_correct' => false]);
        Answer::create(['question_id' => 17, 'answer_text' => 'CONDITION', 'is_correct' => false]);
    }
}
