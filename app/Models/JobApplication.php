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
        'company_id', 'job_title', 'job_description', 'status', 'posted_at', 'submitted_at', 'responded_at',
        'preferred', 'salary_lower', 'salary_upper', 'remote', 'source',
    ];

    protected $casts = [
        'posted_at' => 'date',
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

    public function coverLetters(): HasMany
    {
        return $this->hasMany(CoverLetter::class);
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(JobApplicationStatusEvent::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}
