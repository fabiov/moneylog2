<?php

namespace App\Filament\Resources\ProvisionResource\Pages;

use App\Filament\Resources\ProvisionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProvision extends CreateRecord
{
    protected static string $resource = ProvisionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return array_merge($data, ['user_id' => auth()->id()]);
    }
}
