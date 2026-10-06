<?php

namespace App\Services\Sifak;

use App\Models\JadwalConflict;
use App\Models\JadwalKuliah;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ScheduleConflictService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * @return Collection<int, JadwalConflict>
     */
    public function detect(JadwalKuliah $schedule, bool $persist = true): Collection
    {
        if ($persist) {
            JadwalConflict::query()
                ->where('jadwal_kuliah_id', $schedule->id)
                ->where('resolved', false)
                ->delete();
        }

        $conflicts = collect();
        $query = JadwalKuliah::query()
            ->where('id', '!=', $schedule->id)
            ->where('day_of_week', $schedule->day_of_week)
            ->whereIn('status', config('academic_rules.schedule.active_statuses'))
            ->where(function ($builder) use ($schedule) {
                $builder->where('starts_at', '<', $schedule->ends_at)
                    ->where('ends_at', '>', $schedule->starts_at);
            });

        (clone $query)
            ->where('dosen_id', $schedule->dosen_id)
            ->get()
            ->each(fn (JadwalKuliah $other) => $conflicts->push($this->makeConflict($schedule, 'DOSEN_CONFLICT', $other, 'Dosen memiliki jadwal overlap.', $persist)));

        if ($schedule->ruangan_id) {
            (clone $query)
                ->where('ruangan_id', $schedule->ruangan_id)
                ->get()
                ->each(fn (JadwalKuliah $other) => $conflicts->push($this->makeConflict($schedule, 'RUANG_CONFLICT', $other, 'Ruangan sudah digunakan pada slot ini.', $persist)));
        }

        if ($schedule->kelas_kuliah_id) {
            (clone $query)
                ->where('kelas_kuliah_id', $schedule->kelas_kuliah_id)
                ->get()
                ->each(fn (JadwalKuliah $other) => $conflicts->push($this->makeConflict($schedule, 'KELAS_CONFLICT', $other, 'Kelas memiliki jadwal overlap.', $persist)));
        }

        $capacityConflict = $schedule->ruangan && $schedule->kelasKuliah && $schedule->ruangan->capacity < $schedule->kelasKuliah->jumlah_peserta;
        if ($capacityConflict) {
            $conflicts->push($this->makeConflict($schedule, 'CAPACITY_CONFLICT', null, 'Kapasitas ruangan lebih kecil dari jumlah peserta.', $persist));
        }

        $schedule->update(['conflict_status' => $conflicts->where('severity', 'error')->isEmpty() ? 'clear' : 'conflict']);

        if ($conflicts->isNotEmpty()) {
            $this->audit->record('SCHEDULE_CONFLICT_DETECTED', 'M4', $schedule, [], ['conflicts' => $conflicts->pluck('conflict_type')->all()]);
        }

        return $conflicts;
    }

    public function resolve(JadwalConflict $conflict): JadwalConflict
    {
        $old = $conflict->toArray();
        $conflict->update([
            'resolved' => true,
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ]);

        $this->audit->record('SCHEDULE_CONFLICT_RESOLVED', 'M4', $conflict, $old, $conflict->fresh()->toArray());

        return $conflict->fresh();
    }

    private function makeConflict(JadwalKuliah $schedule, string $type, ?JadwalKuliah $other, string $message, bool $persist): JadwalConflict
    {
        $payload = [
            'tenant_id' => $schedule->tenant_id,
            'jadwal_kuliah_id' => $schedule->id,
            'conflict_type' => $type,
            'conflict_with_id' => $other?->id,
            'severity' => 'error',
            'message' => $message,
            'resolved' => false,
        ];

        return $persist ? JadwalConflict::create($payload) : new JadwalConflict($payload);
    }
}
