<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'type'
    ];

    // Cours
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Questions
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Résultats
    public function results()
    {
        return $this->hasMany(Result::class);
    }
}