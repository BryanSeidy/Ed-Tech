<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'order'
    ];

    // Cours du module
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Leçons
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}