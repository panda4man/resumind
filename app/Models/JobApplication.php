<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name', 'job_title', 'job_description', 'cover_letter_path', 'status', 'submitted_at', 'responded_at',
        'preferred', 'salary_lower', 'salary_upper', 'website', 'glassdoor', 'stack', 'remote', 'source',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'responded_at' => 'datetime',
        'preferred' => 'boolean',
        'remote' => 'boolean',
        'salary_lower' => 'integer',
        'salary_upper' => 'integer',
    ];

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ApplicationQuestion::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}
