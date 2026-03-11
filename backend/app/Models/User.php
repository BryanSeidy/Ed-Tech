<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS ELOQUENT
    |--------------------------------------------------------------------------
    */

    // Enseignant : un user peut créer plusieurs cours
    public function coursesTeaching()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    // Inscriptions aux cours
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Cours suivis par l'étudiant
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments');
    }

    // Résultats des évaluations
    public function results()
    {
        // return $this->hasMany(Result::class);
    }

    // Certificats obtenus
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

}