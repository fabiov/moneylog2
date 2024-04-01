<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static self find(int $id)
 *
 * @property bool $provisioning
 * @property int $payday
 */
class Setting extends Model
{
    use HasFactory;
}
