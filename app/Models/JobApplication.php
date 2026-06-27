<?php

namespace App\Models;

use App\Enums\JobApplicationStatusesEnum;
use App\Enums\StatusEventName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobApplication extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $application): void {
            $application->status ??= JobApplicationStatusesEnum::Prospecting->value;
        });
    }

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

    public function latestStatusEvent(): ?JobApplicationStatusEvent
    {
        return $this->statusEvents()
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->first();
    }

    public function currentStatus(): JobApplicationStatusesEnum
    {
        return $this->mapEventToStatus($this->latestStatusEvent()?->event_name);
    }

    /**
     * @return array<string, string>
     */
    public function allowedNextStatusEvents(): array
    {
        $events = match ($this->latestStatusEvent()?->event_name) {
            null => [StatusEventName::Submitted],
            StatusEventName::Submitted => [
                StatusEventName::Responded,
                StatusEventName::Interviewing,
                StatusEventName::Withdrawn,
                StatusEventName::Rejected,
            ],
            StatusEventName::Responded => [
                StatusEventName::Interviewing,
                StatusEventName::Withdrawn,
                StatusEventName::Rejected,
            ],
            StatusEventName::Interviewing => [
                StatusEventName::Offer,
                StatusEventName::Rejected,
                StatusEventName::Withdrawn,
            ],
            StatusEventName::Offer,
            StatusEventName::Rejected,
            StatusEventName::Withdrawn => [],
        };

        return collect($events)->mapWithKeys(fn (StatusEventName $event) => [
            $event->value => $event->name,
        ])->all();
    }

    public function syncStatusFromEvents(): void
    {
        $this->forceFill([
            'status' => $this->currentStatus()->value,
        ])->saveQuietly();
    }

    private function mapEventToStatus(?StatusEventName $event): JobApplicationStatusesEnum
    {
        return match ($event) {
            StatusEventName::Submitted,
            StatusEventName::Responded => JobApplicationStatusesEnum::Applied,
            StatusEventName::Interviewing => JobApplicationStatusesEnum::Interviewing,
            StatusEventName::Offer => JobApplicationStatusesEnum::Offer,
            StatusEventName::Rejected => JobApplicationStatusesEnum::Rejected,
            StatusEventName::Withdrawn => JobApplicationStatusesEnum::Withdrawn,
            null => JobApplicationStatusesEnum::Prospecting,
        };
    }
}
