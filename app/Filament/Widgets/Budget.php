<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class Budget extends BaseWidget
{
    protected function getStats(): array
    {
        $remainingDays = $this->remainingDays();
        $remainingBudget = $this->remainingBudget();

        return [
            BaseWidget\Stat::make('Remaining to spend',number_format($remainingBudget, 2, ',', '.') . ' €')
                ->color($remainingBudget ? 'success' : 'danger')
                ->description('Money left to spend in the month')
                ->descriptionIcon('heroicon-o-currency-euro'),

            BaseWidget\Stat::make('Remaining to days', $remainingDays)
                ->color($remainingBudget ? 'success' : 'danger')
                ->description('Days remaining before the next paycheck')
                ->descriptionIcon('heroicon-o-calendar'),

            BaseWidget\Stat::make('Daily budget', number_format($remainingBudget / $remainingDays, 2, ',', '.') .' €')
                ->color($remainingBudget ? 'success' : 'danger')
                ->description('Average daily budget')
                ->descriptionIcon('heroicon-o-currency-euro'),
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
        $userId = auth()->id();

        $setting = Setting::find($userId);

        $accountTotal = (float) DB::table('movements')
            ->join('accounts', 'movements.account_id', '=', 'accounts.id')
            ->where('accounts.user_id', '=', $userId)
            ->where('accounts.status', '<>', 'closed')
            ->sum('movements.amount');

        if (!$setting->provisioning) {
            return $accountTotal;
        }

        $provisionTotal = (float) DB::table('provisions')
            ->where('user_id', '=', $userId)
            ->sum('amount');

        $categorizedTotal = (float) DB::table('movements')
            ->join('accounts', 'movements.account_id', '=', 'accounts.id')
            ->where('accounts.user_id', '=', $userId)
            ->whereNotNull('movements.category_id')
            ->sum('movements.amount');

        return $accountTotal - $provisionTotal - $categorizedTotal;
    }
}
