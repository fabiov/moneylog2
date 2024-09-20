<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property ?Category $category
 * @property Account $account
 * @property Carbon $date
 * @property float $amount
 * @property int $id
 * @property string $description
 *
 * @method static Builder where(string $column, mixed $operator)
 */
class Movement extends Model
{
    use HasFactory;

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public static function mostUsedAccountId(int $userId): ?int
    {
        $qb = DB::table('movements')
            ->select(DB::raw('COUNT(*) AS account_count, account_id'))
            ->join('accounts', 'movements.account_id', '=', 'accounts.id')
            ->where('user_id', $userId)
            ->where('accounts.status', '<>', 'closed')
            ->groupBy('account_id')
            ->orderBy('account_count', 'DESC');

        /** @var ?stdClass $model */
        $model = $qb->first();

        return $model?->account_id;
    }

    /**
     * @return array<float>
     */
    public static function getTrend(int $accountId): array
    {
        $start = Carbon::now()->subMonth();
        $stop = Carbon::now();

        $data = [];
        for ($d = $start; $d < $stop; $d->addDay()) {
            $data[] = Type::float(Movement::where('account_id', $accountId)->where('date', '<', $d)->sum('amount'));
        }

        return $data;
    }
}
