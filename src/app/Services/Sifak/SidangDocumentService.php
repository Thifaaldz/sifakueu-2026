<?php

namespace App\Services\Sifak;

use App\Models\SidangMinute;
use App\Models\SidangRegistration;
use App\Models\StoredFile;
use App\Services\Shared\Audit\AuditService;
use App\Services\Shared\Storage\TenantStorageService;
use Illuminate\Support\Facades\Auth;

class SidangDocumentService
{
    public function __construct(
        private readonly TenantStorageService $storage,
        private readonly AuditService $audit,
    ) {}

    public function generateMinutes(SidangRegistration $registration): SidangMinute
    {
        $content = "Berita Acara Sidang\n\nNomor: {$registration->registration_number}\nMahasiswa: {$registration->mahasiswa?->name}\nNIM: {$registration->mahasiswa?->nim}\nJenis: {$registration->type?->name}\nHasil: {$registration->result?->decision}\n";
        $relative = ($registration->mahasiswa?->nim ?? $registration->id) . '/minutes/sidang_minutes_' . now()->format('YmdHis') . '.txt';
        $path = $this->storage->put('sidang', $relative, $content);

        $file = StoredFile::create([
            'tenant_id' => $registration->tenant_id,
            'module' => 'sidang',
            'owner_type' => SidangRegistration::class,
            'owner_id' => (string) $registration->id,
            'original_name' => 'berita_acara_sidang.txt',
            'stored_name' => basename($path),
            'disk' => config('filesystems.default'),
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => strlen($content),
            'checksum' => hash('sha256', $content),
            'version' => 1,
            'status' => 'active',
            'uploaded_by' => Auth::id(),
        ]);

        $minute = SidangMinute::updateOrCreate(
            ['tenant_id' => $registration->tenant_id, 'sidang_registration_id' => $registration->id],
            ['document_file_id' => $file->id, 'generated_at' => now(), 'generated_by' => Auth::id(), 'status' => 'generated']
        );

        $this->audit->record('SIDANG_MINUTES_GENERATED', 'M1', $minute, [], $minute->toArray());

        return $minute;
    }
}
