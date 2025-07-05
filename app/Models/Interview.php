<?php

namespace App\Models;

use App\Actions\InterviewLengthToHumanLabel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'interview_date',
        'type', // e.g. Technical, Panel, CEO Chat
        'format', // e.g. Zoom, Phone, In-Person
        'length_minutes',
        'notes',
    ];

    protected $casts = [
        'interview_date' => 'datetime'
    ];

    public function lengthHumanReadable(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => (new InterviewLengthToHumanLabel)->handle($this)
        );
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
