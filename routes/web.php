<?php

use App\Models\Resume;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::redirect('/', '/admin');

Route::get('/admin/resumes/{resume}/file', function (Resume $resume) {
    if (! request()->user()) {
        return redirect()->route('filament.admin.auth.login');
    }

    abort_unless(request()->user()->canAccessPanel(Filament::getPanel('admin')), 403);

    $disk = Storage::disk(config('filament.default_filesystem_disk'));
    $path = $resume->file_path;

    abort_unless($disk->exists($path), 404);

    $filename = sprintf('%s.pdf', Str::slug($resume->name) ?: 'resume');

    return $disk->response(
        $path,
        $filename,
        ['Content-Type' => 'application/pdf'],
        'inline',
    );
})->name('admin.resumes.file');
