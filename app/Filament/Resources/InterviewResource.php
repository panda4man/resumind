<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InterviewResource\Pages;
use App\Models\Interview;
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
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getSharedColumns())
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
}
