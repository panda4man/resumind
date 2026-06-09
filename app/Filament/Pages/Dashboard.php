<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PendingApplicationsWidget;
use App\Filament\Widgets\RecentRejectionsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            PendingApplicationsWidget::class,
            RecentRejectionsWidget::class,
        ];
    }
}
