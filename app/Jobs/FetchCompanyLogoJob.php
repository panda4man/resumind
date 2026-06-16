<?php

namespace App\Jobs;

use App\Actions\FetchCompanyLogo;
use App\Models\Company;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class FetchCompanyLogoJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 30;

    public function __construct(public readonly Company $company) {}

    public function middleware(): array
    {
        return [new WithoutOverlapping("fetch-company-logo:{$this->company->id}")];
    }

    public function handle(FetchCompanyLogo $action): void
    {
        $action->handle($this->company);
    }
}
