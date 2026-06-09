<?php

namespace App\Filament\Resources\CoverLetterResource\Pages;

use App\Filament\Resources\CoverLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoverLetter extends EditRecord
{
    protected static string $resource = CoverLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
