<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Type;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * @property Collection<Category> $categories
 * @property Setting $setting
 * @property int $id
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

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
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
}
