<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Type;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property int $id
 * @property int $user_id
 * @property string $name
 */
#[ScopedBy([UserScope::class])]
class Category extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Movement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function average(int $months): float
    {
        $fromDate = Carbon::now()->subMonths($months);
        $firstMovementDate = Carbon::parse(Type::string(Movement::where('category_id', $this->id)->min('date')));

        if ($firstMovementDate > $fromDate) {
            $fromDate = $firstMovementDate;
            $months = $firstMovementDate->diffInMonths(Carbon::now());
        }

        return round(
            Movement::where('category_id', $this->id)->where('date', '>=', $fromDate)->sum('amount') / $months,
            2
        );
    }
}
