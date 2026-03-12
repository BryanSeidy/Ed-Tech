<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    // public function courses()
    // {
    //     return $this->hasMany(Course::class, 'instructor_id');
    // }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
                    ->withPivot('enrolled_at')
                    ->orderBy('enrollments.created_at', 'desc');
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
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

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
