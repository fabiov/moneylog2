<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static self find(int $id)
 *
 * @property bool $provisioning
 * @property int $month
 * @property int $payday
 * @property int $months
 */
class Setting extends Model
{
    use HasFactory;
}
