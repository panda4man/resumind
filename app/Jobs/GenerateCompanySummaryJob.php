<?php

namespace App\Jobs;

use App\Actions\GenerateCompanySummary;
use App\Models\Company;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateCompanySummaryJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(public readonly Company $company) {}

    public function handle(GenerateCompanySummary $action): void
    {
        $action->handle($this->company);
    }
}
