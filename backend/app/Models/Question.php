<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'question',
        'type'
    ];

    // Evaluation
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    // Réponses
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}