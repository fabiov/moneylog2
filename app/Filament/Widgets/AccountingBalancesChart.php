<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AccountingBalancesChart extends ChartWidget
{
    protected static ?string $heading = 'Accounting Balances';

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        /** @var User $user */
        $user = auth()->user();

        $data = DB::table('accounts')
            ->select(['accounts.name', DB::raw('SUM(movements.amount) AS total')])
            ->leftJoin('movements', 'accounts.id', '=', 'movements.account_id')
            ->where('accounts.user_id', $user->id)
            ->where('accounts.status', 'highlight')
            ->groupBy('accounts.id')
            ->orderBy('total', 'DESC')
            ->get()
            ->toArray();

        return [
            'labels' => array_map(fn ($item): string => $item instanceof \stdClass ? $item->name : '', $data),
            'datasets' => [
                [
                    'label' => 'My First Dataset',
                    'data' => array_map(fn ($item) => $item instanceof \stdClass ? $item->total : 0, $data),
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
