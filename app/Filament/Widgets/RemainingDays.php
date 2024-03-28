<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class RemainingDays extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            BaseWidget\Stat::make('Remaining to spend', '565,80 €')
                ->chart([1,32,54,46])
                ->chartColor('success')
                ->color('danger')
                ->description('descrizione')
                ->descriptionIcon('heroicon-o-document-text'),
            BaseWidget\Stat::make('Remaining to days', $this->remainingDays())
                ->description('days remaining before the next paycheck')
                ->descriptionIcon('heroicon-o-calendar'),
            BaseWidget\Stat::make('Daily budget', '47,15 €'),
        ];
    }

    private function remainingDays(): int
    {
        $setting = Setting::find(auth()->id());
        $today = (int) date('j');

        return $today < $setting->payday ? $setting->payday - $today : intval(date('t')) - $today + $setting->payday;
    }
}
