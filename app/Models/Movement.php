<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Type;
use Carbon\Carbon;
use DateInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 */
class Movement extends Model
{
    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
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
    public static function getTrend(int $accountId, Carbon $start, Carbon $stop, DateInterval $interval): array
    {
        $data = [];
        for ($d = clone $start; $d < $stop; $d->add($interval)) {
            $data[] = Type::float(Movement::where('account_id', $accountId)->where('date', '<', $d)->sum('amount'));
        }

        return $data;
    }
}
