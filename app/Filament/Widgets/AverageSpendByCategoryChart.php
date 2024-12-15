<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class AverageSpendByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Average monthly spending by category';

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $data = array_values(array_filter($user->categories
            ->map(function (Model $category) use ($user): array {
                /** @var Category $category */
                return [
                    'average' => $category->average($user->setting->months),
                    'name' => $category->name,
                ];
            })
            ->toArray(), fn ($category): bool => is_array($category) && $category['average']));

        usort($data, fn ($a, $b) => $a['average'] <=> $b['average']);

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
