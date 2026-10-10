<?php

namespace App\Filament\Admin\Resources\PemetaanMkCplResource\Pages;

use App\Filament\Admin\Resources\PemetaanMkCplResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPemetaanMkCpl extends EditRecord
{
    protected static string $resource = PemetaanMkCplResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
