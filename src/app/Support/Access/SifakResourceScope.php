<?php

namespace App\Support\Access;

use App\Models\Alert;
use App\Models\BebanDosen;
use App\Models\DokumenTa;
use App\Models\Dosen;
use App\Models\DosenLokasi;
use App\Models\DosenPendidikan;
use App\Models\DosenPengalamanIndustri;
use App\Models\DosenPreferensiMk;
use App\Models\DosenProfil;
use App\Models\DosenPublikasi;
use App\Models\DosenSertifikasi;
use App\Models\JadwalKuliah;
use App\Models\JadwalConflict;
use App\Models\JadwalHistory;
use App\Models\JadwalKonsultasi;
use App\Models\Keahlian;
use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\KrsValidationResult;
use App\Models\Mahasiswa;
use App\Models\MatriksKesesuaian;
use App\Models\MataKuliah;
use App\Models\PendaftaranSidang;
use App\Models\PenawaranMataKuliah;
use App\Models\PeriodeKrs;
use App\Models\PlottingDosen;
use App\Models\RekomendasiPengampu;
use App\Models\RiwayatMengajar;
use App\Models\RepositoryItem;
use App\Models\RevisionCycle;
use App\Models\SidangAssignment;
use App\Models\SidangFile;
use App\Models\SidangMinute;
use App\Models\SidangRegistration;
use App\Models\SidangRequirement;
use App\Models\SidangResult;
use App\Models\SidangRevision;
use App\Models\SidangRubric;
use App\Models\SidangSchedule;
use App\Models\SidangScore;
use App\Models\SidangType;
use App\Models\Surat;
use App\Models\TaApproval;
use App\Models\TaComment;
use App\Models\TaDocument;
use App\Models\TaDocumentVersion;
use App\Models\TaProgressLog;
use App\Models\TaReview;
use App\Models\TaSection;
use App\Models\TugasAkhir;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class SifakResourceScope
{
    public static function apply(Builder $query, ?string $model): Builder
    {
        $user = auth()->user();
        $panelId = Filament::getCurrentPanel()?->getId();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (in_array($panelId, ['super-admin', 'admin', 'pimpinan'], true)) {
            return $query;
        }

        return match ($panelId) {
            'mahasiswa' => self::forMahasiswaPanel($query, $model, $user->id),
            'dosen' => self::forDosenPanel($query, $model, $user),
            default => $query,
        };
    }

    private static function forMahasiswaPanel(Builder $query, ?string $model, int $userId): Builder
    {
        $mahasiswaId = self::mahasiswaIdForUser($userId);

        return match ($model) {
            Mahasiswa::class => $query->where('user_id', $userId),
            Krs::class,
            PendaftaranSidang::class,
            DokumenTa::class,
            Alert::class => $mahasiswaId ? $query->where('mahasiswa_id', $mahasiswaId) : $query->whereRaw('1 = 0'),
            KrsDetail::class,
            KrsValidationResult::class => $mahasiswaId ? $query->whereHas('krs', fn (Builder $builder) => $builder->where('mahasiswa_id', $mahasiswaId)) : $query->whereRaw('1 = 0'),
            Surat::class => $query->where('requester_id', $userId),
            PenawaranMataKuliah::class => $mahasiswaId
                ? $query->whereHas('programStudi.mahasiswas', fn (Builder $builder) => $builder->where('mahasiswas.id', $mahasiswaId))
                : $query->whereRaw('1 = 0'),
            KelasKuliah::class => $mahasiswaId
                ? $query->whereHas('krsDetails.krs', fn (Builder $builder) => $builder->where('mahasiswa_id', $mahasiswaId))
                : $query->whereRaw('1 = 0'),
            JadwalHistory::class => $mahasiswaId
                ? $query->whereHas('jadwalKuliah.kelasKuliah.krsDetails.krs', fn (Builder $builder) => $builder->where('mahasiswa_id', $mahasiswaId))
                : $query->whereRaw('1 = 0'),
            JadwalKuliah::class,
            MataKuliah::class,
            Dosen::class => $query,
            JadwalKonsultasi::class => $query->whereHas('dosen', fn (Builder $builder) => $builder->where('status', 'active')),
            DosenLokasi::class => $query->whereRaw('1 = 0'),
            TugasAkhir::class => $mahasiswaId ? $query->where('mahasiswa_id', $mahasiswaId) : $query->whereRaw('1 = 0'),
            SidangRegistration::class => $mahasiswaId ? $query->where('mahasiswa_id', $mahasiswaId) : $query->whereRaw('1 = 0'),
            SidangAssignment::class,
            SidangSchedule::class,
            SidangResult::class,
            SidangRevision::class,
            SidangMinute::class,
            SidangFile::class,
            SidangScore::class => $mahasiswaId ? $query->whereHas('registration', fn (Builder $builder) => $builder->where('mahasiswa_id', $mahasiswaId)) : $query->whereRaw('1 = 0'),
            SidangType::class,
            SidangRequirement::class,
            SidangRubric::class => $query,
            RevisionCycle::class,
            TaProgressLog::class,
            RepositoryItem::class => $mahasiswaId ? $query->whereHas('tugasAkhir', fn (Builder $builder) => $builder->where('mahasiswa_id', $mahasiswaId)) : $query->whereRaw('1 = 0'),
            TaDocument::class => $mahasiswaId ? $query->whereHas('tugasAkhir', fn (Builder $builder) => $builder->where('mahasiswa_id', $mahasiswaId)) : $query->whereRaw('1 = 0'),
            TaDocumentVersion::class,
            TaReview::class,
            TaComment::class => $mahasiswaId ? $query->whereHas('version.document.tugasAkhir', fn (Builder $builder) => $builder->where('mahasiswa_id', $mahasiswaId)) : $query->whereRaw('1 = 0'),
            TaApproval::class => $mahasiswaId ? $query->whereHas('document.tugasAkhir', fn (Builder $builder) => $builder->where('mahasiswa_id', $mahasiswaId)) : $query->whereRaw('1 = 0'),
            TaSection::class => $query,
            default => $query->whereRaw('1 = 0'),
        };
    }

    private static function forDosenPanel(Builder $query, ?string $model, $user): Builder
    {
        $dosenId = self::dosenIdForUser($user->id);

        if (! $dosenId) {
            return $query->whereRaw('1 = 0');
        }

        $assignedMahasiswaIds = PendaftaranSidang::query()
            ->where(function (Builder $builder) use ($dosenId) {
                $builder
                    ->where('pembimbing_id', $dosenId)
                    ->orWhere('penguji_1_id', $dosenId)
                    ->orWhere('penguji_2_id', $dosenId);
            })
            ->pluck('mahasiswa_id')
            ->merge(SidangRegistration::query()
                ->whereHas('assignments', fn (Builder $builder) => $builder->where('dosen_id', $dosenId))
                ->pluck('mahasiswa_id'))
            ->unique()
            ->values()
            ->all();

        return match ($model) {
            Dosen::class => $query->where('user_id', $user->id),
            Keahlian::class,
            MataKuliah::class => $query,
            JadwalKuliah::class => $query->where('dosen_id', $dosenId),
            KrsDetail::class,
            KrsValidationResult::class => $user->hasRole('dosen_pa')
                ? $query
                : $query->whereHas('krs', fn (Builder $builder) => $builder->whereIn('mahasiswa_id', $assignedMahasiswaIds)),
            PenawaranMataKuliah::class,
            PeriodeKrs::class => $query,
            KelasKuliah::class => $query->whereHas('plottingDosens', fn (Builder $builder) => $builder->where('dosen_id', $dosenId)),
            PlottingDosen::class => $query->where('dosen_id', $dosenId),
            JadwalHistory::class,
            JadwalConflict::class => $query->whereHas('jadwalKuliah', fn (Builder $builder) => $builder->where('dosen_id', $dosenId)),
            DosenProfil::class,
            DosenPendidikan::class,
            DosenSertifikasi::class,
            DosenPublikasi::class,
            DosenPengalamanIndustri::class,
            RiwayatMengajar::class,
            DosenPreferensiMk::class,
            BebanDosen::class,
            MatriksKesesuaian::class,
            RekomendasiPengampu::class,
            JadwalKonsultasi::class,
            DosenLokasi::class => $query->where('dosen_id', $dosenId),
            PendaftaranSidang::class => $query->where(function (Builder $builder) use ($dosenId) {
                $builder
                    ->where('pembimbing_id', $dosenId)
                    ->orWhere('penguji_1_id', $dosenId)
                    ->orWhere('penguji_2_id', $dosenId);
            }),
            SidangRegistration::class => $query->whereHas('assignments', fn (Builder $builder) => $builder->where('dosen_id', $dosenId)),
            SidangAssignment::class,
            SidangSchedule::class,
            SidangResult::class,
            SidangRevision::class,
            SidangMinute::class,
            SidangFile::class => $query->whereHas('registration.assignments', fn (Builder $builder) => $builder->where('dosen_id', $dosenId)),
            SidangScore::class => $query->where(function (Builder $builder) use ($dosenId) {
                $builder->where('examiner_id', $dosenId)
                    ->orWhereHas('registration.assignments', fn (Builder $assignmentQuery) => $assignmentQuery->where('dosen_id', $dosenId));
            }),
            SidangType::class,
            SidangRequirement::class,
            SidangRubric::class => $query,
            DokumenTa::class => $query->whereIn('mahasiswa_id', $assignedMahasiswaIds),
            Mahasiswa::class => $user->hasRole('dosen_pa')
                ? $query
                : $query->whereIn('id', $assignedMahasiswaIds),
            Krs::class => $user->hasRole('dosen_pa')
                ? $query
                : $query->whereRaw('1 = 0'),
            Alert::class => $user->hasRole('dosen_pa')
                ? $query
                : $query->where(function (Builder $builder) use ($user, $assignedMahasiswaIds) {
                    $builder
                        ->where('assigned_to', $user->id)
                        ->orWhereIn('mahasiswa_id', $assignedMahasiswaIds);
                }),
            TugasAkhir::class => $query->where(function (Builder $builder) use ($dosenId) {
                $builder->where('pembimbing_1_id', $dosenId)->orWhere('pembimbing_2_id', $dosenId);
            }),
            RevisionCycle::class,
            TaProgressLog::class,
            RepositoryItem::class => $query->whereHas('tugasAkhir', fn (Builder $builder) => $builder
                ->where('pembimbing_1_id', $dosenId)
                ->orWhere('pembimbing_2_id', $dosenId)),
            TaDocument::class => $query->whereHas('tugasAkhir', fn (Builder $builder) => $builder
                ->where('pembimbing_1_id', $dosenId)
                ->orWhere('pembimbing_2_id', $dosenId)),
            TaDocumentVersion::class,
            TaReview::class,
            TaComment::class => $query->whereHas('version.document.tugasAkhir', fn (Builder $builder) => $builder
                ->where('pembimbing_1_id', $dosenId)
                ->orWhere('pembimbing_2_id', $dosenId)),
            TaApproval::class => $query->whereHas('document.tugasAkhir', fn (Builder $builder) => $builder
                ->where('pembimbing_1_id', $dosenId)
                ->orWhere('pembimbing_2_id', $dosenId)),
            TaSection::class => $query,
            default => $query->whereRaw('1 = 0'),
        };
    }

    private static function mahasiswaIdForUser(int $userId): ?int
    {
        return Mahasiswa::query()->where('user_id', $userId)->value('id');
    }

    private static function dosenIdForUser(int $userId): ?int
    {
        return Dosen::query()->where('user_id', $userId)->value('id');
    }
}
