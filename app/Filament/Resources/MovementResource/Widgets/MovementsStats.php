<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovementResource\Widgets;

use App\Filament\Resources\MovementResource\Pages\ListMovements;
use App\Filament\Resources\Shop\OrderResource\Pages\ListOrders;
use App\Models\Account;
use App\Models\Movement;
use App\Models\Shop\Order;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Number;

class MovementsStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListMovements::class;
    }

    protected function getStats(): array
    {
        $widget = [];
        foreach (Account::where('status', '<>', 'closed')->get() as $account) {
            $balance = (float) Movement::where('account_id', $account->id)->sum('amount');
            $widget[] = Stat::make($account->name, Number::currency($balance, 'EUR', 'it'));
        }
        return $widget;
    }
}
