<?php

namespace App\Services\Shared\File;

use App\Models\FileVersion;
use App\Models\StoredFile;
use App\Services\Shared\Audit\AuditService;
use App\Services\Shared\Storage\TenantStorageService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FileService
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'jpg', 'jpeg', 'png', 'xlsx', 'csv'];

    public function __construct(
        private readonly TenantStorageService $storage,
        private readonly AuditService $audit,
    ) {}

    public function upload(
        UploadedFile $file,
        string $module,
        Model|string|null $owner = null,
        array $options = []
    ): StoredFile {
        $this->validate($file, $options);

        $tenant = app(TenantContext::class)->get();
        $extension = strtolower($file->getClientOriginalExtension());
        $storedName = Str::uuid() . '.' . $extension;
        $relativeDirectory = trim((string) ($options['directory'] ?? now()->format('Y/m')), '/');
        $path = $this->storage->path($module, $relativeDirectory . '/' . $storedName);
        $disk = $options['disk'] ?? config('filesystems.default');

        Storage::disk($disk)->putFileAs(dirname($path), $file, $storedName);

        $storedFile = StoredFile::create([
            'tenant_id' => $tenant?->id,
            'module' => $module,
            'owner_type' => $owner instanceof Model ? $owner::class : $owner,
            'owner_id' => $owner instanceof Model ? (string) $owner->getKey() : null,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize() ?: 0,
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'version' => 1,
            'status' => $options['status'] ?? 'temp',
            'uploaded_by' => Auth::id(),
        ]);

        $this->createVersion($storedFile, 'Initial upload');
        $this->audit->record('UPLOAD', $module, $storedFile, [], $storedFile->toArray());

        return $storedFile;
    }

    public function createVersion(StoredFile $storedFile, ?string $note = null): FileVersion
    {
        return FileVersion::create([
            'tenant_id' => $storedFile->tenant_id,
            'stored_file_id' => $storedFile->id,
            'version' => $storedFile->version,
            'path' => $storedFile->path,
            'checksum' => $storedFile->checksum,
            'size' => $storedFile->size,
            'created_by' => Auth::id(),
            'note' => $note,
        ]);
    }

    public function archive(StoredFile $storedFile): StoredFile
    {
        $old = $storedFile->toArray();
        $storedFile->update(['status' => 'archived']);
        $this->audit->record('DELETE_FILE', $storedFile->module, $storedFile, $old, $storedFile->fresh()->toArray());

        return $storedFile;
    }

    private function validate(UploadedFile $file, array $options): void
    {
        $extensions = $options['extensions'] ?? self::ALLOWED_EXTENSIONS;
        $maxKilobytes = (int) ($options['max_kb'] ?? 10240);
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $extensions, true)) {
            throw new InvalidArgumentException('FILE_TYPE_NOT_ALLOWED');
        }

        if (($file->getSize() ?: 0) > $maxKilobytes * 1024) {
            throw new InvalidArgumentException('FILE_TOO_LARGE');
        }

        if (str_contains($file->getClientOriginalName(), '..')) {
            throw new InvalidArgumentException('INVALID_FILENAME');
        }
    }
}
