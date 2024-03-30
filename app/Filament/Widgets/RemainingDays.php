<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class RemainingDays extends BaseWidget
{
    protected function getStats(): array
    {
        $remainingBudget = $this->remainingBudget();

        return [
            BaseWidget\Stat::make('Remaining to spend',number_format($remainingBudget, 2, ',', '.') . ' €')
                ->chart([1, 32, 54, 46])
                ->chartColor('success')
                ->color($remainingBudget ? 'success' : 'danger')
                ->description('Money left to spend in the month')
                ->descriptionIcon('heroicon-o-currency-euro'),

            BaseWidget\Stat::make('Remaining to days', $this->remainingDays())
                ->description('Days remaining before the next paycheck')
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

    private function remainingBudget(): float
    {
        $accountBalance = (float)DB::table('movements')
            ->join('accounts', 'movements.account_id', '=', 'accounts.id')
            ->where('accounts.user_id', '=', auth()->id())
            ->where('accounts.status', '<>', 'closed')
            ->sum('movements.amount');

        return $accountBalance;
    }
}
