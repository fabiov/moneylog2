<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Number;

class Balances extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        /** @var User $user */
        $user = auth()->user();

        $accountsBalance = $user->accountsBalance();
        $provisionBalance = $user->provisionBalance();
        $averageExpensesPerCategory = abs(array_sum(array_column($user->averageExpensesPerCategory(), 'average')));

        return [
            BaseWidget\Stat::make('Total balance of all accounts', Number::currency($accountsBalance, 'EUR', 'it'))
                ->color($accountsBalance > 0 ? 'success' : 'danger')
                ->description('Money left to spend in the month')
                ->descriptionIcon('heroicon-o-currency-euro'),
            BaseWidget\Stat::make('Provision balance', Number::currency($provisionBalance, 'EUR', 'it'))
                ->color($provisionBalance > 0 ? 'success' : 'danger')
                ->description('Provision balance')
                ->descriptionIcon('heroicon-o-currency-euro'),
            BaseWidget\Stat::make('Expenses', Number::currency($averageExpensesPerCategory, 'EUR', 'it'))
                ->color($provisionBalance > 0 ? 'success' : 'danger')
                ->description('Total average monthly expenses')
                ->descriptionIcon('heroicon-o-currency-euro'),
        ];
    }
}
