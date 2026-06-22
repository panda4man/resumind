<?php

namespace App\Filament\Resources\JobApplicationResource\RelationManagers;

use App\Enums\StatusEventName;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class StatusEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'statusEvents';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('event_name')
                ->options(collect(StatusEventName::cases())->mapWithKeys(fn (StatusEventName $event) => [
                    $event->value => $event->name,
                ]))
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: function (Unique $rule): Unique {
                        return $rule->where('job_application_id', $this->ownerRecord->getKey());
                    },
                )
                ->required(),
            DateTimePicker::make('occurred_at')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('event_name')
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('event_name')->badge(),
                TextColumn::make('occurred_at')->dateTime('M d, Y H:i')->sortable(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
