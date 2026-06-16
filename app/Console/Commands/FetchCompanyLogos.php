<?php

namespace App\Console\Commands;

use App\Jobs\FetchCompanyLogoJob;
use App\Models\Company;
use Illuminate\Console\Command;

class FetchCompanyLogos extends Command
{
    protected $signature = 'companies:fetch-logos';

    protected $description = 'Dispatch logo fetch jobs for all companies without logos';

    public function handle(): int
    {
        $companies = Company::whereNull('logo_url')
            ->whereNotNull('website')
            ->get();

        $count = $companies->count();

        if ($count === 0) {
            $this->info('No companies need logo fetching.');
            return self::SUCCESS;
        }

        foreach ($companies as $company) {
            FetchCompanyLogoJob::dispatch($company);
        }

        $this->info("Dispatched {$count} logo fetch jobs.");

        return self::SUCCESS;
    }
}
