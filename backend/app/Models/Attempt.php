<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'attempted_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'attempted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function course()
    {
        return $this->hasOneThrough(Course::class, Quiz::class, 'id', 'id', 'quiz_id', 'course_id');
    }
}
