<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Setting;
use App\Models\User;
use DateTime;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use stdClass;

class AverageSpendByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $setting = Setting::find($user->id);
        $data = $this->getAverages($user->id, new DateTime(sprintf('-%d months', $setting->months)));

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

    /**
     * @return array<array{average: ?float, name: string, id: int, active: bool}>
     */
    private function getAverages(int $userId, DateTime $since): array
    {
        $qb = DB::table('categories')
            ->select([
                'categories.id AS category_id',
                DB::raw('SUM(movements.amount) AS amount'),
                DB::raw('MIN(movements.date) AS first_date'),
            ])
            ->join('movements', 'categories.id', '=', 'movements.category_id')
            ->where('categories.user_id', '=', $userId)
            ->where('categories.active', '=', 1)
            ->where('movements.date', '>=', $since->format('Y-m-d'))
            ->groupBy('categories.id');

        $rs = $qb->get();

        $data = [];
        $oldestMovements = $this->oldestMovements($userId);

        foreach ($oldestMovements as $oldestMovement) {
            $average = null;

            $item = $rs->first(fn ($i) => $i->category_id === $oldestMovement->category_id);

            if ($item) {
                $date = $oldestMovement->date < $item->first_date ? $since->format('Y-m-d') : $item->first_date;
                [$y, $m, $d] = explode('-', $date);

                // mesi di differenza
                $firstDateUnixTime = mktime(0, 0, 0, (int) $m, (int) $d, (int) $y);
                $monthDiff = (mktime(0, 0, 0) - $firstDateUnixTime) / 2628000;
                if ($monthDiff) {
                    $average = $item->amount / $monthDiff;
                }
            }
            $data[] = [
                'average' => $average,
                'name' => $oldestMovement->name,
                'id' => $oldestMovement->category_id,
                'active' => $oldestMovement->active,
            ];
        }

        return $data;
    }

    /**
     * @return array<stdClass>
     */
    private function oldestMovements(int $userId): array
    {
        $qb = DB::table('categories')
            ->select([
                'categories.id AS category_id',
                'categories.name',
                DB::raw('MIN(movements.date) AS date'),
                'categories.active',
            ])
            ->leftJoin('movements', 'categories.id', '=', 'movements.category_id')
            ->where('categories.user_id', '=', $userId)
            ->where('categories.active', '=', 1)
            ->groupBy('categories.id');

        return $qb->get()->toArray();
    }
}
