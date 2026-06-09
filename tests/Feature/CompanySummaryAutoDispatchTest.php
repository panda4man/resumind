<?php

namespace Tests\Feature;

use App\Jobs\GenerateCompanySummaryJob;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CompanySummaryAutoDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_dispatches_job_when_created_with_website_and_no_summary(): void
    {
        $company = Company::factory()->create([
            'website' => 'https://acme.example.com',
            'summary' => null,
        ]);

        Queue::assertPushed(
            GenerateCompanySummaryJob::class,
            fn (GenerateCompanySummaryJob $job) => $job->company->is($company),
        );
    }

    public function test_does_not_dispatch_when_summary_already_present(): void
    {
        Company::factory()->create([
            'website' => 'https://acme.example.com',
            'summary' => 'Existing summary text.',
        ]);

        Queue::assertNotPushed(GenerateCompanySummaryJob::class);
    }

    public function test_does_not_dispatch_on_update_when_summary_added_to_website_company(): void
    {
        $company = Company::factory()->create([
            'website' => 'https://acme.example.com',
            'summary' => null,
        ]);

        // Clear the queue from the create dispatch.
        Queue::fake();

        // Now update to add a summary — should NOT dispatch again.
        $company->update(['summary' => 'Manually entered summary.']);

        Queue::assertNotPushed(GenerateCompanySummaryJob::class);
    }

    public function test_does_not_dispatch_when_website_is_absent(): void
    {
        Company::factory()->create([
            'website' => null,
            'summary' => null,
        ]);

        Queue::assertNotPushed(GenerateCompanySummaryJob::class);
    }

    public function test_dispatches_on_update_when_website_added_to_summaryless_company(): void
    {
        // Create without website — no dispatch expected.
        $company = Company::factory()->create([
            'website' => null,
            'summary' => null,
        ]);
        Queue::assertNotPushed(GenerateCompanySummaryJob::class);

        // Add website — should dispatch.
        $company->update(['website' => 'https://acme.example.com']);

        Queue::assertPushed(
            GenerateCompanySummaryJob::class,
            fn (GenerateCompanySummaryJob $job) => $job->company->is($company),
        );
    }

    public function test_does_not_redispatch_after_summary_is_populated(): void
    {
        // First save: website present, summary blank → dispatches once.
        $company = Company::factory()->create([
            'website' => 'https://acme.example.com',
            'summary' => null,
        ]);

        // Simulate the action writing the summary back.
        $company->update(['summary' => 'Generated summary.']);

        // Only the original create should have pushed a job.
        Queue::assertPushed(GenerateCompanySummaryJob::class, 1);
    }
}
