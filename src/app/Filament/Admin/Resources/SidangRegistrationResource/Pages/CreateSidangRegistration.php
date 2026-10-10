<?php

namespace App\Filament\Admin\Resources\SidangRegistrationResource\Pages;

use App\Filament\Admin\Resources\SidangRegistrationResource;
use App\Models\SidangRegistration;
use App\Services\Sifak\SidangRequirementService;
use Illuminate\Support\Str;
use Filament\Resources\Pages\CreateRecord;

class CreateSidangRegistration extends CreateRecord
{
    protected static string $resource = SidangRegistrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['registration_number'] ??= 'SIDANG-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        $data['status'] ??= 'draft';

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var SidangRegistration $record */
        $record = $this->record;

        app(SidangRequirementService::class)->validate($record);
    }
}
