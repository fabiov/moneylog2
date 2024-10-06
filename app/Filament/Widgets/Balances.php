<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Number;

class Balances extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->setting->provisioning ? 3 : 2;
    }

    protected function getStats(): array
    {
        /** @var User $user */
        $user = auth()->user();

        $accountsBalance = $user->accountsBalance();

        $averageExpensesPerCategory = 0;
        /** @var Category $category */
        foreach ($user->categories as $category) {
            $averageExpensesPerCategory += $category->average($user->setting->months);
        }
        $averageExpensesPerCategory = abs($averageExpensesPerCategory);

        $widgets = [
            BaseWidget\Stat::make('Total balance of all accounts', Number::currency($accountsBalance, 'EUR', 'it'))
                ->color($accountsBalance > 0 ? 'success' : 'danger')
                ->description('Money left to spend in the month')
                ->descriptionIcon('heroicon-o-currency-euro'),
        ];

        if ($user->setting->provisioning) {
            $provisionBalance = $user->provisionBalance();
            $widgets[] = BaseWidget\Stat::make('Provision balance', Number::currency($provisionBalance, 'EUR', 'it'))
                ->color($provisionBalance > 0 ? 'success' : 'danger')
                ->description('Provision balance')
                ->descriptionIcon('heroicon-o-currency-euro');
        }

        $widgets[] = BaseWidget\Stat::make('Expenses', Number::currency($averageExpensesPerCategory, 'EUR', 'it'))
            ->color('danger')
            ->description('Total average monthly expenses')
            ->descriptionIcon('heroicon-o-currency-euro');

        return $widgets;
    }
}
