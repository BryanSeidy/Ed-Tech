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
        'teacher_id'
    ];

    // Enseignant du cours
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
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