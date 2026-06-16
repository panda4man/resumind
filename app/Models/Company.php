<?php

namespace App\Models;

use App\Enums\CompanyTypesEnum;
use App\Observers\CompanyObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(CompanyObserver::class)]
class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'website', 'glassdoor', 'stack', 'type', 'summary', 'size', 'logo_url'];

    protected $casts = [
        'type' => CompanyTypesEnum::class,
    ];

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
