<?php

namespace App\Filament\Resources;

use App\Enums\JobApplicationStatusesEnum;
use App\Filament\Resources\JobApplicationResource\Pages;
use App\Filament\Resources\JobApplicationResource\RelationManagers\InterviewsRelationManager;
use App\Models\JobApplication;
use Filament\Forms\Components;
use Filament\Forms\Form;
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

                Components\TextInput::make('company_name')
                    ->required()
                    ->maxLength(255),

                Components\TextInput::make('job_title')
                    ->maxLength(255),

                Components\Select::make('status')
                    ->options([
                        JobApplicationStatusesEnum::Applied->value => JobApplicationStatusesEnum::Applied->name,
                        JobApplicationStatusesEnum::Interviewing->value => JobApplicationStatusesEnum::Interviewing->name,
                        JobApplicationStatusesEnum::Offer->value => JobApplicationStatusesEnum::Offer->name,
                        JobApplicationStatusesEnum::Rejected->value => JobApplicationStatusesEnum::Rejected->name,
                        JobApplicationStatusesEnum::Withdrawn->value => JobApplicationStatusesEnum::Withdrawn->name,
                    ])
                    ->required(),

                Components\DatePicker::make('submitted_at'),
                Components\DatePicker::make('responded_at'),
                Components\Textarea::make('job_description'),

                Components\Section::make('Details')
                    ->columns(2)
                    ->schema([
                        Components\Toggle::make('preferred'),
                        Components\Toggle::make('remote'),
                        Components\TextInput::make('salary_lower')
                            ->numeric()->prefix('$')->suffix('k'),
                        Components\TextInput::make('salary_upper')
                            ->numeric()->prefix('$')->suffix('k'),
                        Components\TextInput::make('website')
                            ->url()->maxLength(255),
                        Components\TextInput::make('glassdoor')
                            ->url()->maxLength(255),
                        Components\TextInput::make('stack')
                            ->maxLength(255),
                        Components\TextInput::make('source')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')->searchable()->sortable(),
                TextColumn::make('job_title')->searchable()->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('submitted_at')->date(),
                TextColumn::make('responded_at')->date(),
                TextColumn::make('resume.name')->label('Resume'),
                Tables\Columns\IconColumn::make('preferred')->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('remote')->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('source')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stack')
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
                        JobApplicationStatusesEnum::Applied->value => JobApplicationStatusesEnum::Applied->name,
                        JobApplicationStatusesEnum::Interviewing->value => JobApplicationStatusesEnum::Interviewing->name,
                        JobApplicationStatusesEnum::Offer->value => JobApplicationStatusesEnum::Offer->name,
                        JobApplicationStatusesEnum::Rejected->value => JobApplicationStatusesEnum::Rejected->name,
                        JobApplicationStatusesEnum::Withdrawn->value => JobApplicationStatusesEnum::Withdrawn->name,
                    ]),
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
            InterviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobApplications::route('/'),
            'create' => Pages\CreateJobApplication::route('/create'),
            'edit' => Pages\EditJobApplication::route('/{record}/edit'),
        ];
    }
}
