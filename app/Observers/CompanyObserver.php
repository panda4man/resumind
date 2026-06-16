<?php

namespace App\Observers;

use App\Jobs\FetchCompanyLogoJob;
use App\Jobs\GenerateCompanySummaryJob;
use App\Models\Company;

class CompanyObserver
{
    public function saved(Company $company): void
    {
        if (blank($company->website)) {
            return;
        }

        if (blank($company->summary)) {
            GenerateCompanySummaryJob::dispatch($company);
        }

        if (blank($company->logo_url)) {
            FetchCompanyLogoJob::dispatch($company);
        }
    }
}
