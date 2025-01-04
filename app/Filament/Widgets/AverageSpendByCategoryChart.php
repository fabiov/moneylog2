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
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(201, 203, 207)',
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
