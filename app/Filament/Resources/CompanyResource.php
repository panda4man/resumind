<?php

namespace App\Filament\Resources;

use App\Enums\CompanyTypesEnum;
use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('website')
                    ->url()
                    ->maxLength(255),

                TextInput::make('glassdoor')
                    ->url()
                    ->maxLength(255),

                TextInput::make('stack')
                    ->maxLength(255),

                Select::make('type')
                    ->options(collect(CompanyTypesEnum::cases())->mapWithKeys(fn ($e) => [$e->value => $e->name]))
                    ->nullable()
                    ->placeholder('Select type'),

                Textarea::make('summary')
                    ->rows(8)
                    ->columnSpanFull()
                    ->placeholder('AI-generated or manually entered company summary…')
                    ->helperText('Use "Generate AI Summary" to auto-populate.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('job_applications_count')
                    ->label('Applications')
                    ->counts('jobApplications')
                    ->sortable(),
                TextColumn::make('website')->limit(40)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stack')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')->badge()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('jobApplications');
    }
}
