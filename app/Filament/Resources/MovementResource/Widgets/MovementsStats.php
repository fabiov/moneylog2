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
use Illuminate\Support\Facades\Auth;
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
        $startDate = empty($fromFilterValue) ? self::getOldestMovement() : Carbon::parse($fromFilterValue);

        $untilFilterValue = Type::nullableString(Arr::get((array) $this->tableFilters, 'date.date_until'));
        $endDate = empty($fromFilterValue) ? Carbon::now() : Carbon::parse($untilFilterValue);

        $widgets = [];
        /** @var Account $account */
        foreach (Account::where('status', '<>', 'closed')->get() as $account) {
            $trend = Movement::where('account_id', $account->id)
                    ->selectRaw('YEAR(date) AS year, SUM(amount) AS total_amount')
                    ->where('date', '>=', $startDate)
                    ->where('date', '<=', $endDate)
                    ->groupBy('year')
                    ->orderBy('year')
                    ->pluck('total_amount', 'year')
                    ->map('floatval')
                    ->toArray();

            $widgets[] = Stat::make($account->name, Number::currency(array_sum($trend), 'EUR', 'it'))
                ->chart($trend)
                ->chartColor(reset($trend) > end($trend) ? 'danger' : 'success');
        }

        return $widgets;
    }

    private static function getOldestMovement(): Carbon
    {
        return Carbon::parse(Movement::join(
            'accounts',
            fn ($join) => $join->on('accounts.id', '=', 'movements.account_id')->where('accounts.user_id', Auth::id())
        )->min('movements.date'));
    }
}
