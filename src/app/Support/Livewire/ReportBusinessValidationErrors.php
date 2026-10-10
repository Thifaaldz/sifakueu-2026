<?php

namespace App\Support\Livewire;

use Filament\Notifications\Notification;
use Filament\Pages\BasePage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
use Livewire\ComponentHook;

/**
 * ValidationException dari service (mis. "syarat belum lengkap") memakai key yang bukan field form,
 * sehingga Filament tidak menampilkannya dan modal action terlihat "diam". Hook ini menampilkan
 * pesan tersebut sebagai notifikasi.
 */
class ReportBusinessValidationErrors extends ComponentHook
{
    public function exception($e, $stopPropagation)
    {
        if ($e instanceof UniqueConstraintViolationException && $this->component instanceof BasePage) {
            Notification::make()
                ->title('Data sudah ada')
                ->body('Data yang sama sudah tersimpan sebelumnya. Ubah isian atau edit data yang sudah ada.')
                ->danger()
                ->persistent()
                ->send();

            $stopPropagation();

            return;
        }

        if (! $e instanceof ValidationException || ! $this->component instanceof BasePage) {
            return;
        }

        $messages = collect($e->errors())
            ->reject(fn ($messages, string $key) => str_starts_with($key, 'data.')
                || str_starts_with($key, 'mountedTableActionsData.')
                || str_starts_with($key, 'mountedActionsData.')
                || str_starts_with($key, 'mountedFormComponentActionsData.'))
            ->flatten();

        if ($messages->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Proses tidak dapat dilanjutkan')
            ->body($messages->implode("\n"))
            ->danger()
            ->persistent()
            ->send();
    }
}
