<?php

namespace App\Filament\Admin\Resources\MahasiswaProfileResource\Pages;

use App\Filament\Admin\Resources\MahasiswaProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswaProfile extends EditRecord
{
    protected static string $resource = MahasiswaProfileResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
