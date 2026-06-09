<?php

namespace App\Filament\Resources\CoverLetterResource\Pages;

use App\Filament\Resources\CoverLetterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCoverLetter extends CreateRecord
{
    protected static string $resource = CoverLetterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
