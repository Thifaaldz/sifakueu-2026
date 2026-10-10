<?php

namespace App\Filament\Admin\Resources\SidangScheduleResource\Pages;

use App\Filament\Admin\Resources\SidangScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSidangSchedule extends EditRecord
{
    protected static string $resource = SidangScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $moved = collect(['tanggal', 'jam_mulai', 'jam_selesai', 'ruangan_id'])
            ->contains(fn (string $field) => (string) ($data[$field] ?? '') !== (string) $this->record->getRawOriginal($field));

        if ($moved && $this->record->status === 'conflict' && ($data['status'] ?? 'conflict') === 'conflict') {
            $data['status'] = 'rescheduled';
        }

        return $data;
    }
}
