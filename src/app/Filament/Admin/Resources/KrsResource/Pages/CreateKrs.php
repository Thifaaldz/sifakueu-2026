<?php

namespace App\Filament\Admin\Resources\KrsResource\Pages;

use App\Filament\Admin\Resources\KrsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKrs extends CreateRecord
{
    protected static string $resource = KrsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->hasRole('mahasiswa')) {
            $data['mahasiswa_id'] = auth()->user()->mahasiswa?->id;
            $data['status'] = 'draft';
        }

        $data['status'] ??= 'draft';

        return $data;
    }
}
