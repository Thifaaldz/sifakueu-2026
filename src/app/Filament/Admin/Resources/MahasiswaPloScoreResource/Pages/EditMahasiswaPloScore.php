<?php

namespace App\Filament\Admin\Resources\MahasiswaPloScoreResource\Pages;

use App\Filament\Admin\Resources\MahasiswaPloScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswaPloScore extends EditRecord
{
    protected static string $resource = MahasiswaPloScoreResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
