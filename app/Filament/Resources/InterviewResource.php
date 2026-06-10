<?php

namespace App\Filament\Resources;

use App\Enums\InterviewFormatsEnum;
use App\Enums\InterviewTypesEnum;
use App\Filament\Resources\InterviewResource\Pages;
use App\Models\Interview;
use App\Models\JobApplication;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InterviewResource extends Resource
{
    protected static ?string $model = Interview::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Select::make('job_application_id')
                    ->relationship(
                        name: 'jobApplication',
                        titleAttribute: 'job_title',
                        modifyQueryUsing: fn ($query) => $query->with('company'),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => "{$record->job_title} @ {$record->company->name}"
                    )
                    ->getSearchResultsUsing(function (string $search) {
                        return JobApplication::with('company')
                            ->where('job_title', 'like', "%{$search}%")
                            ->orWhereHas('company', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn ($record) => [$record->id => "{$record->job_title} @ {$record->company->name}"]);
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Job Application'),
                ...static::getSharedFormFields(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jobApplication.company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                ...static::getSharedColumns(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInterviews::route('/'),
            'create' => Pages\CreateInterview::route('/create'),
            'edit' => Pages\EditInterview::route('/{record}/edit'),
        ];
    }

    public static function getSharedColumns(): array
    {
        return [
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('interview_date')->date()->sortable(),
            TextColumn::make('type')->badge(),
            TextColumn::make('format')->badge(),
            TextColumn::make('length_human_readable')->label('Duration'),
        ];
    }

    public static function getSharedFormFields(): array
    {
        return [
            Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Name'),
            Components\DateTimePicker::make('interview_date')
                ->required()
                ->label('Date'),
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
                    collect(range(5, 180, 5))->mapWithKeys(fn ($v) => [
                        $v => str_pad(floor($v / 60).'h ', 4, ' ', STR_PAD_RIGHT).str_pad($v % 60 .'m', 3, '0', STR_PAD_LEFT),
                    ])
                )
                ->required(),
            Components\Textarea::make('notes'),
        ];
    }
}
