<?php

declare(strict_types=1);

namespace App\Policies;

class SettingPolicy
{
    public function create(): bool
    {
        return false;
    }

    public function delete(): bool
    {
        return false;
    }
}
