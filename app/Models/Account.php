<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 */
#[ScopedBy([UserScope::class])]
class Account extends Model
{
    use HasFactory;

    /**
     * @return HasMany
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}
