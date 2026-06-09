<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoverLetterResource\Pages;
use App\Models\CoverLetter;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoverLetterResource extends Resource
{
    protected static ?string $model = CoverLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form->schema(static::getSharedFormFields());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getSharedColumns())
            ->filters([])
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoverLetters::route('/'),
            'create' => Pages\CreateCoverLetter::route('/create'),
            'edit' => Pages\EditCoverLetter::route('/{record}/edit'),
        ];
    }

    public static function getSharedFormFields(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Cover Letter Name'),

            FileUpload::make('file_path')
                ->label('Cover Letter PDF')
                ->directory('cover-letters')
                ->acceptedFileTypes(['application/pdf'])
                ->required(),
        ];
    }

    public static function getSharedColumns(): array
    {
        return [
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('file_path')
                ->label('File')
                ->limit(30)
                ->copyable(),
            TextColumn::make('created_at')->date()->sortable(),
        ];
    }
}
