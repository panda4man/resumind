<?php

namespace App\Models;

use App\Enums\StatusEventName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicationStatusEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id',
        'event_name',
        'occurred_at',
    ];

    protected $casts = [
        'event_name' => StatusEventName::class,
        'occurred_at' => 'datetime',
    ];

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
