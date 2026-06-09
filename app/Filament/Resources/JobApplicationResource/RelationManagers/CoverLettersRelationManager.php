<?php

namespace App\Filament\Resources\JobApplicationResource\RelationManagers;

use App\Filament\Resources\CoverLetterResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CoverLettersRelationManager extends RelationManager
{
    protected static string $relationship = 'coverLetters';

    public function form(Form $form): Form
    {
        return $form->schema(CoverLetterResource::getSharedFormFields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(CoverLetterResource::getSharedColumns())
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
