<?php

namespace App\Console\Commands;

use App\Enums\StatusEventName;
use App\Models\JobApplication;
use App\Models\JobApplicationStatusEvent;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;

class BackfillSubmittedStatusEvents extends Command
{
    protected $signature = 'job-applications:backfill-submitted-status-events';

    protected $description = 'Backfill submitted status events for legacy job applications with submitted_at timestamps';

    public function handle(): int
    {
        $ignoredWithoutSubmittedAt = JobApplication::query()
            ->whereNull('submitted_at')
            ->count();

        $applicationsWithSubmittedAt = JobApplication::query()
            ->whereNotNull('submitted_at');

        $alreadyHadSubmittedEvent = (clone $applicationsWithSubmittedAt)
            ->whereHas('statusEvents', fn (Builder $query) => $query->where('event_name', StatusEventName::Submitted->value))
            ->count();

        $missingSubmittedEvent = (clone $applicationsWithSubmittedAt)
            ->whereDoesntHave('statusEvents', fn (Builder $query) => $query->where('event_name', StatusEventName::Submitted->value));

        $created = 0;
        $duplicateSkips = 0;

        $missingSubmittedEvent
            ->orderBy('id')
            ->chunkById(100, function ($applications) use (&$created, &$duplicateSkips): void {
                foreach ($applications as $application) {
                    try {
                        JobApplicationStatusEvent::query()->create([
                            'job_application_id' => $application->id,
                            'event_name' => StatusEventName::Submitted,
                            'occurred_at' => $application->submitted_at,
                        ]);

                        $created++;
                    } catch (QueryException $exception) {
                        if ($this->isDuplicateSubmittedEventException($exception)) {
                            $duplicateSkips++;

                            continue;
                        }

                        throw $exception;
                    }
                }
            });

        $skippedWithExistingSubmittedEvent = $alreadyHadSubmittedEvent + $duplicateSkips;

        $this->info("Created {$created} submitted status events.");
        $this->info("Skipped {$skippedWithExistingSubmittedEvent} applications that already had submitted events.");
        $this->info("Ignored {$ignoredWithoutSubmittedAt} applications without submitted_at.");

        return self::SUCCESS;
    }

    private function isDuplicateSubmittedEventException(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'unique')
            || str_contains($message, 'duplicate')
            || str_contains($message, '1062')
            || str_contains($message, '23000')
            || str_contains($message, '23505');
    }
}
