<?php

namespace App\Filament\Resources\JobApplicationResource\RelationManagers;

use App\Enums\InterviewFormatsEnum;
use App\Enums\InterviewTypesEnum;
use App\Filament\Resources\InterviewResource;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InterviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'interviews';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Components\DatePicker::make('interview_date')
                    ->required(),
                Components\Select::make('type')
                    ->options(collect(InterviewTypesEnum::cases())->mapWithKeys(function ($enum) {
                        return [$enum->value => $enum->name];
                    }))
                    ->required(),
                Components\Select::make('format')
                    ->options(collect(InterviewFormatsEnum::cases())->mapWithKeys(function ($enum) {
                        return [$enum->value => $enum->name];
                    }))
                    ->required(),
                Components\Select::make('length_minutes')
                    ->label('Duration (minutes)')
                    ->options(
                        collect(range(5, 180, 5))->mapWithKeys(fn($v) => [
                            $v => str_pad(floor($v / 60) . 'h ', 4, ' ', STR_PAD_RIGHT) . str_pad($v % 60 . 'm', 3, '0', STR_PAD_LEFT)
                        ])
                    )
                    ->required(),
                Components\Textarea::make('notes')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(InterviewResource::getSharedColumns())
            ->filters([
                //
            ])
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
