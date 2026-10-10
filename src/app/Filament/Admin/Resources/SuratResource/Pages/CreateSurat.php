<?php

namespace App\Filament\Admin\Resources\SuratResource\Pages;

use App\Filament\Admin\Resources\SuratResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSurat extends CreateRecord
{
    protected static string $resource = SuratResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requester_id'] ??= auth()->id();
        $data['status'] ??= 'DRAFT';

        return $data;
    }
}
