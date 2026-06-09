<?php

namespace App\Console\Commands;

use App\Enums\JobApplicationStatusesEnum;
use App\Models\Company;
use App\Models\JobApplication;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportWyldeHunt extends Command
{
    protected $signature = 'import:wylde-hunt {path : Path to the .xlsx file}';

    protected $description = 'Import the 2025 Wylde Hunt job-application spreadsheet into job_applications';

    /**
     * Spreadsheet header (lowercased) => job_applications attribute / logical field.
     */
    private const HEADER_MAP = [
        'company' => 'company',
        'status' => 'status',
        'preferred' => 'preferred',
        'salary lower (k)' => 'salary_lower',
        'salary upper (k)' => 'salary_upper',
        'website' => 'website',
        'glassdoor' => 'glassdoor',
        'job' => 'job_title',
        'stack' => 'stack',
        'remote' => 'remote',
        'applied' => 'source',
        'date applied' => 'submitted_at',
        'responded at' => 'responded_at',
    ];

    /**
     * Spreadsheet status value => canonical enum value.
     */
    private const STATUS_MAP = [
        'pending' => JobApplicationStatusesEnum::Applied,
        'denied' => JobApplicationStatusesEnum::Rejected,
        'active' => JobApplicationStatusesEnum::Interviewing,
        'skipped' => JobApplicationStatusesEnum::Withdrawn,
    ];

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $rows = $reader->load($path)->getActiveSheet()
            ->toArray(null, true, false, true); // formatData=false -> raw serial dates

        if (empty($rows)) {
            $this->error('Spreadsheet is empty.');

            return self::FAILURE;
        }

        // Row 1 is the header. Build header-text => column-letter map.
        $headerRow = array_shift($rows);
        $columns = [];
        foreach ($headerRow as $letter => $text) {
            $key = strtolower(trim((string) $text));
            if (isset(self::HEADER_MAP[$key])) {
                $columns[self::HEADER_MAP[$key]] = $letter;
            }
        }

        if (! isset($columns['company'])) {
            $this->error('Could not find a "Company" column in the header row.');

            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $columns, &$created, &$updated, &$skipped) {
            foreach ($rows as $rowNumber => $row) {
                $get = fn (string $field) => isset($columns[$field]) ? ($row[$columns[$field]] ?? null) : null;

                $companyName = $this->cleanString($get('company'));

                if ($companyName === null) {
                    $skipped++;

                    continue; // blank row
                }

                $jobTitle = $this->cleanString($get('job_title'));
                if ($jobTitle === null) {
                    $this->warn("Row {$rowNumber}: '{$companyName}' has no job title; left null.");
                }

                $rawStatus = strtolower(trim((string) $get('status')));
                $status = self::STATUS_MAP[$rawStatus] ?? null;
                if ($status === null) {
                    $this->warn("Row {$rowNumber}: '{$companyName}' has unknown status '{$rawStatus}'; defaulting to applied.");
                    $status = JobApplicationStatusesEnum::Applied;
                }

                $companyModel = Company::firstOrCreate(
                    ['name' => $companyName],
                    array_filter([
                        'website' => $this->cleanString($get('website')),
                        'glassdoor' => $this->cleanString($get('glassdoor')),
                        'stack' => $this->cleanString($get('stack')),
                    ], fn ($v) => $v !== null),
                );

                $attributes = [
                    'status' => $status->value,
                    'preferred' => $this->toBool($get('preferred')),
                    'remote' => $this->toBool($get('remote')),
                    'salary_lower' => $this->toSalary($get('salary_lower')),
                    'salary_upper' => $this->toSalary($get('salary_upper')),
                    'source' => $this->cleanString($get('source')),
                    'submitted_at' => $this->toDate($get('submitted_at')),
                    'responded_at' => $this->toDate($get('responded_at')),
                ];

                $model = JobApplication::updateOrCreate(
                    ['company_id' => $companyModel->id, 'job_title' => $jobTitle],
                    $attributes,
                );

                $model->wasRecentlyCreated ? $created++ : $updated++;
            }
        });

        $this->info("Import complete: {$created} created, {$updated} updated, {$skipped} blank rows skipped.");

        return self::SUCCESS;
    }

    private function cleanString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function toBool(mixed $value): bool
    {
        return strtolower(trim((string) ($value ?? ''))) === 'yes';
    }

    private function toSalary(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : (int) round((float) $value);
    }

    private function toDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
        }

        return Carbon::parse($value);
    }
}
