<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'file_path'];

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
