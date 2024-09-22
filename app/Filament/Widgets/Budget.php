<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Number;

class Budget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        /** @var User $user */
        $user = auth()->user();

        $remainingDays = $this->remainingDays();
        $remainingBudget = $user->remainingBudget();
        $color = $remainingBudget ? 'success' : 'danger';

        return [
            BaseWidget\Stat::make('Remaining to spend', Number::currency($remainingBudget, 'EUR', 'it'))
                ->color($color)
                ->description('Money left to spend in the month')
                ->descriptionIcon('heroicon-o-currency-euro'),
            BaseWidget\Stat::make('Remaining to days', $remainingDays)
                ->color($color)
                ->description('Days remaining before the next paycheck')
                ->descriptionIcon('heroicon-o-calendar'),
            BaseWidget\Stat::make('Daily budget', Number::currency($remainingBudget / $remainingDays, 'EUR', 'it'))
                ->color($color)
                ->description('Average daily budget')
                ->descriptionIcon('heroicon-o-currency-euro'),
        ];
    }

    private function remainingDays(): int
    {
        /** @var User $user */
        $user = auth()->user();
        $today = (int) date('j');

        return $today < $user->setting->payday
            ? $user->setting->payday - $today
            : intval(date('t')) - $today + $user->setting->payday;
    }
}
