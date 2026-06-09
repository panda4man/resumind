<?php

namespace App\Jobs;

use App\Actions\GenerateCompanySummary;
use App\Models\Company;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class GenerateCompanySummaryJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(public readonly Company $company) {}

    public function middleware(): array
    {
        return [new WithoutOverlapping('generate-company-summary')];
    }

    public function handle(GenerateCompanySummary $action): void
    {
        $action->handle($this->company);
    }
}
