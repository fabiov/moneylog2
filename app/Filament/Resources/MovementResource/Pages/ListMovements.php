<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovementResource\Pages;

use App\Filament\Resources\MovementResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListMovements extends ListRecords
{
    use ExposesTableToWidgets;

    protected ?string $maxContentWidth = 'full';

    protected static string $resource = MovementResource::class;

    protected function getHeaderWidgets(): array
    {
        return MovementResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
