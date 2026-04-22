<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    // Enseignant du cours
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // Alias de compatibilité
    public function teacher()
    {
        return $this->instructor();
    }

    // Modules du cours
    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    // Sessions de classe virtuelle
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    // Evaluations
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    // Etudiants inscrits
    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments');
    }

    // Certificats
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
