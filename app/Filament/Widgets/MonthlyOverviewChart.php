<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Setting;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyOverviewChart extends ChartWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Monthly overview';

    protected static ?string $pollingInterval = null;

    protected function getMaxHeight(): ?string
    {
        return '200px';
    }

    protected function getData(): array
    {
        $setting = Setting::find(auth()->id());
        $currentDay = date('j');

        if ($setting->payday) {
            if ($currentDay < $setting->payday) {
                $remainingDays = $setting->payday - $currentDay;
                $begin = date("Y-m-$setting->payday", (int) strtotime('last month'));
            } else {
                $remainingDays = intval(date('t')) - $currentDay + $setting->payday;
                $begin = date("Y-m-$setting->payday");
            }
            $end = date('Y-m-d', (int) strtotime(($remainingDays - 1) . ' day'));
        } else {
            $begin = date('Y-m-01');
            $end = date('Y-m-t');
        }

        $dailyExpenses = $this->getDailyExpenses($begin, $end);

        return [
            'labels' => array_map(fn ($item) => (new \DateTime($item->date))->format('d M'), $dailyExpenses),
            'datasets' => [
                [
                    'label' => 'Daily expenses',
                    'data' => array_map(fn ($item) => abs((float) $item->amount), $dailyExpenses),
                    'backgroundColor' => [
                        'rgba(255, 0, 0, 0.2)',
                    ],
                    'borderColor' => [
                        'rgb(255, 0, 0)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getDailyExpenses(string $begin, string $end): array
    {
        $dailyExpenses = DB::table('movements')
            ->select(['movements.date', DB::raw('SUM(movements.amount) AS amount')])
            ->join('accounts', 'movements.account_id', '=', 'accounts.id')
            ->where('movements.amount', '<', 0)
            ->whereBetween('movements.date', [$begin, $end])
            ->whereNull('movements.category_id')
            ->where('accounts.user_id', '=', auth()->id())
            ->where('accounts.status', '=', 'highlight')
            ->groupBy('movements.date')
            ->orderBy('movements.date')
            ->get()
            ->toArray();

        $beginFiller = \DateTime::createFromFormat('Y-m-d', $begin);
        $endFiller   = \DateTime::createFromFormat('Y-m-d', $end);

        for ($i = $beginFiller; $i <= $endFiller; $i->modify('+1 day')) {
            /** @var \DateTime $i */
            $day = $i->format('Y-m-d');
            if (!array_filter($dailyExpenses, fn ($item) => $item->date === $day)) {
                $objectDay = new \stdClass();
                $objectDay->date = $day;
                $objectDay->amount = 0;
                $dailyExpenses[] = $objectDay;
            }
        }

        usort($dailyExpenses, fn($a, $b) => $a->date <=> $b->date);

        return $dailyExpenses;
    }
}
