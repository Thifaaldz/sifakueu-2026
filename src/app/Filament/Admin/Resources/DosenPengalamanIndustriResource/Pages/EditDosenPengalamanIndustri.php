<?php

namespace App\Filament\Admin\Resources\DosenPengalamanIndustriResource\Pages;

use App\Filament\Admin\Resources\DosenPengalamanIndustriResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDosenPengalamanIndustri extends EditRecord
{
    protected static string $resource = DosenPengalamanIndustriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
