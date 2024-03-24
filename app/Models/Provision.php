<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Carbon $date
 * @property float $amount
 * @property int $user_id
 * @property string $description
 */
class Provision extends Model
{
    use HasFactory;
}
