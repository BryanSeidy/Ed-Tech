<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'course_id', 'certificate_url', 'issued_at'];

    protected $casts = ['issued_at' => 'datetime'];

    /**
     * Get the user that owns the certificate.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that the certificate is for.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
