<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attempt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'quiz_id', 'score', 'attempted_at'];

    protected $casts = [
        'score' => 'integer',
        'attempted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
    
    public function course()
    {
        return $this->hasOneThrough(Course::class, Quiz::class, 'id', 'id', 'quiz_id', 'course_id');
    }
}
