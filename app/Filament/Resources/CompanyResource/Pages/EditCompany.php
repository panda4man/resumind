<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Jobs\GenerateCompanySummaryJob;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateSummary')
                ->label('Generate AI Summary')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Generate Company Summary')
                ->modalDescription('This will fetch the company website and use AI to write a summary. Any existing summary will be overwritten.')
                ->modalSubmitActionLabel('Generate')
                ->action(function () {
                    $company = $this->getRecord();

                    if (empty($company->website)) {
                        Notification::make()
                            ->title('No website URL')
                            ->body('Add a website URL before generating a summary.')
                            ->warning()
                            ->send();

                        return;
                    }

                    GenerateCompanySummaryJob::dispatch($company);

                    Notification::make()
                        ->title('Summary generation queued')
                        ->body('Reload this page in a few seconds to see the result.')
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
