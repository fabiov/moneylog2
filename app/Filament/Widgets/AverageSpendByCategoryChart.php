<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use DateMalformedStringException;
use Filament\Widgets\ChartWidget;

class AverageSpendByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected static ?string $pollingInterval = null;

    /**
     * @throws DateMalformedStringException
     */
    protected function getData(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $data = array_values(array_filter(
            $user->averageExpensesPerCategory(),
            fn (array $category): bool => (bool) $category['average']
        ));

        return [
            'labels' => array_map(fn ($item) => $item['name'], $data),
            'datasets' => [
                [
                    'label' => 'My First Dataset',
                    'data' => array_map(fn ($item) => $item['average'], $data),
                    'backgroundColor' => [
                        'rgb(054, 162, 235)',
                        'rgb(110, 086, 108)',
                        'rgb(149, 109, 148)',
                        'rgb(195, 172, 170)',
                        'rgb(247, 207, 190)',
                        'rgb(255, 099, 132)',
                        'rgb(255, 205, 086)',
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
