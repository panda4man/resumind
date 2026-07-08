<?php

namespace App\Filament\Resources\ResumeResource\Pages;

use App\Filament\Resources\ResumeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViewResume extends ViewRecord
{
    protected static string $resource = ResumeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewPdf')
                ->label('View PDF')
                ->icon('heroicon-o-document-magnifying-glass')
                ->url(fn (): string => route('admin.resumes.file', $this->getRecord()))
                ->openUrlInNewTab(),
            Actions\Action::make('download')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (): ?StreamedResponse => $this->downloadResume()),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    private function downloadResume(): ?StreamedResponse
    {
        $disk = Storage::disk(config('filament.default_filesystem_disk'));
        $path = $this->getRecord()->file_path;

        if (! $disk->exists($path)) {
            Notification::make()
                ->title('Resume file not found')
                ->danger()
                ->send();

            return null;
        }

        return $disk->download($path, $this->downloadFilename());
    }

    private function downloadFilename(): string
    {
        return sprintf('%s.pdf', Str::slug($this->getRecord()->name) ?: 'resume');
    }
}
