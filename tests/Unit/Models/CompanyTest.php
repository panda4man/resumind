<?php

namespace Tests\Unit\Models;

use App\Enums\CompanyTypesEnum;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\Resume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_company_fillable_attributes(): void
    {
        $company = Company::create([
            'name' => 'Acme Corp',
            'website' => 'https://acme.example.com',
            'glassdoor' => 'https://glassdoor.com/acme',
            'stack' => 'PHP/Laravel',
            'type' => 'saas',
        ]);

        $this->assertEquals('Acme Corp', $company->name);
        $this->assertEquals('https://acme.example.com', $company->website);
        $this->assertEquals('https://glassdoor.com/acme', $company->glassdoor);
        $this->assertEquals('PHP/Laravel', $company->stack);
        $this->assertEquals(CompanyTypesEnum::SaaS, $company->type);
    }

    public function test_company_has_many_job_applications(): void
    {
        $company = Company::factory()->create();
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->for($company)->create();

        $this->assertTrue($company->jobApplications->contains($application));
        $this->assertEquals(1, $company->jobApplications()->count());
    }

    public function test_duplicate_company_names_rejected(): void
    {
        Company::create(['name' => 'Acme Corp']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Company::create(['name' => 'Acme Corp']);
    }

    public function test_duplicate_company_websites_rejected(): void
    {
        Company::create(['name' => 'Acme Corp', 'website' => 'https://acme.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Company::create(['name' => 'Acme Industries', 'website' => 'https://acme.com']);
    }

    public function test_duplicate_website_allows_null(): void
    {
        Company::create(['name' => 'Company 1']);
        Company::create(['name' => 'Company 2']);

        $this->assertCount(2, Company::all());
    }
}
