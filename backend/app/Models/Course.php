<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'instructor_id',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // Enseignant du cours
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function liveSessions(): HasMany
    {
        return $this->hasMany(LiveSession::class);
    }

    // Alias de compatibilité pour les anciennes références aux sessions live.
    public function sessions(): HasMany
    {
        return $this->liveSessions();
    }

    // Evaluations
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    // public function enrollments()
    // Alias de compatibilité
    public function teacher()
    {
        return $this->instructor();
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    // Certificats
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
