<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovementResource\Widgets;

use App\Filament\Resources\MovementResource\Pages\ListMovements;
use App\Helpers\Type;
use App\Models\Account;
use App\Models\Movement;
use Carbon\Carbon;
use DateInterval;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Arr;
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
        $fromFilterValue = Type::nullableString(Arr::get((array) $this->tableFilters, 'date.date_from'));
        $startDate = empty($fromFilterValue) ? Carbon::now()->subYear() : Carbon::parse($fromFilterValue);

        $untilFilterValue = Type::nullableString(Arr::get((array) $this->tableFilters, 'date.date_until'));
        $endDate = empty($fromFilterValue) ? Carbon::now() : Carbon::parse($untilFilterValue);

        $interval = DateInterval::createFromDateString($startDate->diffInYears($endDate) > 2 ? '1 year' : '1 months');

        $widgets = [];
        /** @var Account $account */
        foreach (Account::where('status', '<>', 'closed')->get() as $account) {
            $balance = Type::float(Movement::where('account_id', $account->id)->sum('amount'));

            $trend = Movement::getTrend($account->id, $startDate, $endDate, $interval);

            $widgets[] = Stat::make($account->name, Number::currency($balance, 'EUR', 'it'))
                ->chart(array_map(fn (float $value): int => (int) $value, $trend))
                ->chartColor(reset($trend) > end($trend) ? 'danger' : 'success');
        }

        return $widgets;
    }
}
