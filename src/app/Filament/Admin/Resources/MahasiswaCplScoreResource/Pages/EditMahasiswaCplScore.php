<?php

namespace App\Filament\Admin\Resources\MahasiswaCplScoreResource\Pages;

use App\Filament\Admin\Resources\MahasiswaCplScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswaCplScore extends EditRecord
{
    protected static string $resource = MahasiswaCplScoreResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
