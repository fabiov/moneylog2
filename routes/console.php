<?php

declare(strict_types=1);

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/** @var ClosureCommand $this */
Artisan::command('inspire', fn () => $this->comment(Inspiring::quote()))
    ->purpose('Display an inspiring quote')
    ->hourly();

Schedule::command('emails:send Taylor --force')->daily();
