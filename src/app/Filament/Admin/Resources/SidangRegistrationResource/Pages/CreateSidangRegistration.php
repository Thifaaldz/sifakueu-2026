<?php

namespace App\Filament\Admin\Resources\SidangRegistrationResource\Pages;

use App\Filament\Admin\Resources\SidangRegistrationResource;
use Illuminate\Support\Str;
use Filament\Resources\Pages\CreateRecord;

class CreateSidangRegistration extends CreateRecord
{
    protected static string $resource = SidangRegistrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['registration_number'] ??= 'SIDANG-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

        return $data;
    }
}
