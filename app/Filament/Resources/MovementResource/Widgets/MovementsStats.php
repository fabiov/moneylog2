<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovementResource\Widgets;

use App\Filament\Resources\MovementResource\Pages\ListMovements;
use App\Models\Account;
use App\Models\Movement;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
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
        /** @var Account $account */
        foreach (Account::where('status', '<>', 'closed')->get() as $account) {
            $balance = (float) Movement::where('account_id', $account->id)->sum('amount');
            $trend = Movement::getTrend($account->id);
            $widget[] = Stat::make($account->name, Number::currency($balance, 'EUR', 'it'))
                ->chart(array_map(fn (float $value): int => (int) $value, $trend))
                ->chartColor(reset($trend) > end($trend) ? 'danger' : 'success');
        }

        return $widget;
    }
}
