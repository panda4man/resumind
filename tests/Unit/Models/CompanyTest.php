<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\JobApplication;
use App\Models\Resume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_fillable_attributes(): void
    {
        $company = Company::create([
            'name' => 'Acme Corp',
            'website' => 'https://acme.example.com',
            'glassdoor' => 'https://glassdoor.com/acme',
            'stack' => 'PHP/Laravel',
        ]);

        $this->assertEquals('Acme Corp', $company->name);
        $this->assertEquals('https://acme.example.com', $company->website);
        $this->assertEquals('https://glassdoor.com/acme', $company->glassdoor);
        $this->assertEquals('PHP/Laravel', $company->stack);
    }

    public function test_company_has_many_job_applications(): void
    {
        $company = Company::factory()->create();
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->for($company)->create();

        $this->assertTrue($company->jobApplications->contains($application));
        $this->assertEquals(1, $company->jobApplications()->count());
    }
}
