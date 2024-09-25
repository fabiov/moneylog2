<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Type;
use DateTime;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * @property int $id
 * @property Setting $setting
 */
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class, 'id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function accountsBalance(): float
    {
        return Type::float(DB::table('movements')
            ->join('accounts', 'movements.account_id', '=', 'accounts.id')
            ->where('accounts.user_id', $this->id)
            ->where('accounts.status', '<>', 'closed')
            ->sum('movements.amount'));
    }

    public function provisionBalance(): float
    {
        $provisionTotal = Type::float(Provision::where('user_id', $this->id)->sum('amount'));

        $categorizedTotal = Type::float(DB::table('movements')
            ->join('accounts', 'movements.account_id', '=', 'accounts.id')
            ->where('accounts.user_id', $this->id)
            ->whereNotNull('movements.category_id')
            ->sum('movements.amount'));

        return $provisionTotal + $categorizedTotal;
    }

    public function remainingBudget(): float
    {
        $accountTotal = $this->accountsBalance();

        if (! $this->setting->provisioning) {
            return $accountTotal;
        }

        return $accountTotal - $this->provisionBalance();
    }

    /**
     * @return array<array{average: float, name: string}>
     *
     * @throws \DateMalformedStringException
     */
    public function averageExpensesPerCategory(): array
    {
        $since = new DateTime(sprintf('-%d months', $this->setting->months));

        $oldestMovements = DB::table('categories')
            ->select([
                'categories.id AS category_id',
                'categories.name',
                DB::raw('MIN(movements.date) AS date'),
                'categories.active',
            ])
            ->leftJoin('movements', 'categories.id', '=', 'movements.category_id')
            ->where('categories.user_id', '=', $this->id)
            ->where('categories.active', '=', 1)
            ->groupBy('categories.id')
            ->get();

        $rs = DB::table('categories')
            ->select([
                'categories.id AS category_id',
                DB::raw('SUM(movements.amount) AS amount'),
                DB::raw('MIN(movements.date) AS first_date'),
            ])
            ->join('movements', 'categories.id', '=', 'movements.category_id')
            ->where('categories.user_id', '=', $this->id)
            ->where('categories.active', '=', 1)
            ->where('movements.date', '>=', $since->format('Y-m-d'))
            ->groupBy('categories.id')
            ->get();

        $data = [];
        /** @var stdClass $oldestMovement */
        foreach ($oldestMovements as $oldestMovement) {

            $average = 0;

            /** @var ?stdClass $item */
            $item = $rs->first(fn ($i) => $i instanceof stdClass && $i->category_id === $oldestMovement->category_id);

            if ($item) {
                $date = $oldestMovement->date < $item->first_date ? $since->format('Y-m-d') : $item->first_date;
                [$y, $m, $d] = explode('-', $date);

                // month difference
                $firstDateUnixTime = mktime(0, 0, 0, (int) $m, (int) $d, (int) $y);
                $monthDiff = (mktime(0, 0, 0) - $firstDateUnixTime) / 2628000;
                if ($monthDiff) {
                    $average = $item->amount / $monthDiff;
                }
            }

            $data[] = ['average' => $average, 'name' => $oldestMovement->name];
        }

        return $data;
    }
}
