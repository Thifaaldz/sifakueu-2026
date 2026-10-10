<?php

namespace App\Filament\Admin\Resources\PemetaanCplPloResource\Pages;

use App\Filament\Admin\Resources\PemetaanCplPloResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPemetaanCplPlo extends EditRecord
{
    protected static string $resource = PemetaanCplPloResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
