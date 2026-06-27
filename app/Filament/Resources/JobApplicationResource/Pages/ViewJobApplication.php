<?php

namespace App\Filament\Resources\JobApplicationResource\Pages;

use App\Enums\StatusEventName;
use App\Filament\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;

class ViewJobApplication extends ViewRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('advanceStatus')
                ->label('Advance Status')
                ->visible(fn (): bool => filled($this->getRecord()->allowedNextStatusEvents()))
                ->form([
                    Select::make('event_name')
                        ->label('Next status')
                        ->options(fn (): array => $this->getRecord()->allowedNextStatusEvents())
                        ->required()
                        ->in(array_keys($this->getRecord()->allowedNextStatusEvents())),
                    DateTimePicker::make('occurred_at')
                        ->required()
                        ->default(now()),
                ])
                ->action(function (array $data): void {
                    $this->getRecord()->statusEvents()->create([
                        'event_name' => StatusEventName::from($data['event_name']),
                        'occurred_at' => $data['occurred_at'],
                    ]);

                    $this->record = $this->getRecord()->fresh();
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
