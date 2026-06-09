<?php

namespace App\Observers;

use App\Jobs\GenerateCompanySummaryJob;
use App\Models\Company;

class CompanyObserver
{
    public function saved(Company $company): void
    {
        // Dispatch only when both conditions hold: summary is empty AND website is present.
        if (!blank($company->summary) || blank($company->website)) {
            return;
        }

        GenerateCompanySummaryJob::dispatch($company);
    }
}
