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
        $startDate = empty($this->tableFilters['date']['date_from'])
            ? Carbon::now()->subYear() : Carbon::parse($this->tableFilters['date']['date_from']);

        $endDate = empty($this->tableFilters['date']['date_until'])
            ? Carbon::now() : Carbon::parse($this->tableFilters['date']['date_until']);

        if ($startDate->diffInYears($endDate) > 2) {
            $interval = DateInterval::createFromDateString('1 year');
        } elseif ($startDate->diffInMonths($endDate) > 2) {
            $interval = DateInterval::createFromDateString('1 months');
        } else {
            $interval = DateInterval::createFromDateString('1 day');
        }

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
