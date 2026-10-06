<?php

namespace App\Filament\Admin\Resources\BebanDosenResource\Pages;

use App\Filament\Admin\Resources\BebanDosenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBebanDosen extends EditRecord
{
    protected static string $resource = BebanDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
