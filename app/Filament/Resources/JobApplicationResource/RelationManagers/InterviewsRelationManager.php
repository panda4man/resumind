<?php

namespace App\Filament\Resources\JobApplicationResource\RelationManagers;

use App\Filament\Resources\InterviewResource;
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
            ->schema(InterviewResource::getSharedFormFields());
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
