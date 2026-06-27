<?php

namespace App\Filament\Resources;

use App\Enums\JobApplicationStatusesEnum;
use App\Enums\StatusEventName;
use App\Filament\Resources\JobApplicationResource\Pages;
use App\Filament\Resources\JobApplicationResource\RelationManagers\CoverLettersRelationManager;
use App\Filament\Resources\JobApplicationResource\RelationManagers\InterviewsRelationManager;
use App\Models\JobApplication;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobApplicationResource extends Resource
{
    protected static ?string $model = JobApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Select::make('resume_id')
                    ->relationship('resume', 'name')
                    ->searchable()
                    ->label('Resume'),

                Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Components\TextInput::make('name')->required()->maxLength(255),
                        Components\TextInput::make('website')->url()->maxLength(255),
                        Components\TextInput::make('glassdoor')->url()->maxLength(255),
                        Components\TextInput::make('stack')->maxLength(255),
                    ])
                    ->label('Company'),

                Components\TextInput::make('job_title')
                    ->maxLength(255),

                Components\DatePicker::make('posted_at'),
                Components\DatePicker::make('submitted_at')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->readOnly()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Components\DatePicker $component, ?JobApplication $record): void {
                        $component->state(
                            $record?->statusEvents()
                                ->where('event_name', StatusEventName::Submitted)
                                ->latest('occurred_at')
                                ->value('occurred_at')
                        );
                    }),
                Components\DatePicker::make('responded_at'),
                Components\Textarea::make('job_description')
                    ->rows(8)
                    ->columnSpanFull(),

                Components\Section::make('Details')
                    ->columns(2)
                    ->schema([
                        Components\Toggle::make('preferred'),
                        Components\Toggle::make('remote'),
                        Components\TextInput::make('salary_lower')
                            ->numeric()->prefix('$')->suffix('k'),
                        Components\TextInput::make('salary_upper')
                            ->numeric()->prefix('$')->suffix('k'),
                        Components\TextInput::make('source')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('company.name')->label('Company')->searchable()->sortable(),
                TextColumn::make('company.stack')->label('Tech Stack')->searchable()->wrap(),
                TextColumn::make('job_title')->searchable()->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('posted_at')->date()->sortable(),
                TextColumn::make('submitted_at')->date()->sortable(),
                TextColumn::make('responded_at')->date()->sortable(),
                TextColumn::make('resume.name')->label('Resume'),
                Tables\Columns\IconColumn::make('preferred')->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('remote')->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('source')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('salary_lower')
                    ->formatStateUsing(fn ($state) => $state ? "\${$state}k" : null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('salary_upper')
                    ->formatStateUsing(fn ($state) => $state ? "\${$state}k" : null)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        JobApplicationStatusesEnum::Prospecting->value => JobApplicationStatusesEnum::Prospecting->name,
                        JobApplicationStatusesEnum::Applied->value => JobApplicationStatusesEnum::Applied->name,
                        JobApplicationStatusesEnum::Interviewing->value => JobApplicationStatusesEnum::Interviewing->name,
                        JobApplicationStatusesEnum::Offer->value => JobApplicationStatusesEnum::Offer->name,
                        JobApplicationStatusesEnum::Rejected->value => JobApplicationStatusesEnum::Rejected->name,
                        JobApplicationStatusesEnum::Withdrawn->value => JobApplicationStatusesEnum::Withdrawn->name,
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            InterviewsRelationManager::class,
            CoverLettersRelationManager::class,
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Application')
                    ->schema([
                        TextEntry::make('company.name')
                            ->label('Company'),
                        TextEntry::make('job_title'),
                        TextEntry::make('computed_status')
                            ->label('Current Status')
                            ->badge()
                            ->state(fn (JobApplication $record): string => $record->currentStatus()->value),
                        TextEntry::make('source'),
                        TextEntry::make('posted_at')->date(),
                        TextEntry::make('submitted_at')->dateTime(),
                        TextEntry::make('responded_at')->dateTime(),
                        TextEntry::make('job_description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                InfolistSection::make('Status Timeline')
                    ->schema([
                        RepeatableEntry::make('statusEvents')
                            ->state(fn (JobApplication $record): array => $record->statusEvents
                                ->map(fn ($event): array => [
                                    'event_name' => $event->event_name->value,
                                    'occurred_at' => $event->occurred_at,
                                ])
                                ->all())
                            ->schema([
                                TextEntry::make('event_name')
                                    ->badge(),
                                TextEntry::make('occurred_at')
                                    ->dateTime('M d, Y H:i'),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobApplications::route('/'),
            'create' => Pages\CreateJobApplication::route('/create'),
            'view' => Pages\ViewJobApplication::route('/{record}'),
            'edit' => Pages\EditJobApplication::route('/{record}/edit'),
        ];
    }
}
