<?php

namespace App\Filament\Admin\Resources\CompetencyGapResource\Pages;

use App\Filament\Admin\Resources\CompetencyGapResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompetencyGap extends EditRecord
{
    protected static string $resource = CompetencyGapResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
