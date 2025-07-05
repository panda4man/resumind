<?php

namespace App\Filament\Resources;

use App\Enums\JobApplicationStatusesEnum;
use App\Filament\Resources\JobApplicationResource\Pages;
use App\Filament\Resources\JobApplicationResource\RelationManagers\InterviewsRelationManager;
use App\Models\JobApplication;
use Filament\Forms;
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
                Forms\Components\Select::make('resume_id')
                    ->relationship('resume', 'name')
                    ->searchable()
                    ->label('Resume'),

                Forms\Components\TextInput::make('company_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('job_title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('status')
                    ->options([
                        JobApplicationStatusesEnum::Applied->value => JobApplicationStatusesEnum::Applied->name,
                        JobApplicationStatusesEnum::Interviewing->value => JobApplicationStatusesEnum::Interviewing->name,
                        JobApplicationStatusesEnum::Offer->value => JobApplicationStatusesEnum::Offer->name,
                        JobApplicationStatusesEnum::Rejected->value => JobApplicationStatusesEnum::Rejected->name,
                        JobApplicationStatusesEnum::Withdrawn->value => JobApplicationStatusesEnum::Withdrawn->name,
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('submitted_at'),
                Forms\Components\DatePicker::make('responded_at'),
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
            InterviewsRelationManager::class
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
