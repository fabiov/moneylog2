<?php

declare(strict_types=1);

namespace App\Helpers;

use InvalidArgumentException;
use Stringable;

class Type
{
    public static function float(mixed $value): float
    {
        if (is_scalar($value) || is_null($value)) {
            return (float) $value;
        }

        throw new InvalidArgumentException('Can\'t cast to float');
    }

    public static function nullableString(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        if (is_scalar($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        throw new InvalidArgumentException('Can\'t cast to nullable string');
    }
}
