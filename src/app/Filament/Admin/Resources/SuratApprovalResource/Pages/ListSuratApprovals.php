<?php

namespace App\Filament\Admin\Resources\SuratApprovalResource\Pages;

use App\Filament\Admin\Resources\SuratApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuratApprovals extends ListRecords
{
    protected static string $resource = SuratApprovalResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
