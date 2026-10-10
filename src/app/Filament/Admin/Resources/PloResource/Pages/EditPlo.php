<?php

namespace App\Filament\Admin\Resources\PloResource\Pages;

use App\Filament\Admin\Resources\PloResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlo extends EditRecord
{
    protected static string $resource = PloResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
