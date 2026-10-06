<?php

namespace App\Filament\Admin\Resources\TugasAkhirResource\Pages;

use App\Filament\Admin\Resources\TugasAkhirResource;
use App\Models\TugasAkhir;
use App\Services\Sifak\TaService;
use Filament\Resources\Pages\CreateRecord;

class CreateTugasAkhir extends CreateRecord
{
    protected static string $resource = TugasAkhirResource::class;

    protected function afterCreate(): void
    {
        /** @var TugasAkhir $record */
        $record = $this->record;

        app(TaService::class)->ensureDefaultSections();
        app(TaService::class)->ensureDocuments($record);
    }
}
