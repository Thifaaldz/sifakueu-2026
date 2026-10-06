<?php

namespace App\Services\Sifak;

use App\Models\JadwalHistory;
use App\Models\JadwalKuliah;
use App\Services\Shared\Audit\AuditService;
use App\Services\Shared\Notification\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    public function __construct(
        private readonly ScheduleConflictService $conflicts,
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
    ) {}

    public function validate(JadwalKuliah $schedule)
    {
        return $this->conflicts->detect($schedule);
    }

    public function finalize(JadwalKuliah $schedule): JadwalKuliah
    {
        $conflicts = $this->conflicts->detect($schedule);

        if ($conflicts->where('severity', 'error')->isNotEmpty()) {
            throw ValidationException::withMessages(['jadwal' => 'Jadwal masih memiliki konflik aktif.']);
        }

        $old = $schedule->toArray();
        $schedule->update([
            'status' => 'final',
            'finalized_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        $this->audit->record('SCHEDULE_FINALIZED', 'M4', $schedule, $old, $schedule->fresh()->toArray());

        return $schedule->fresh();
    }

    public function reschedule(JadwalKuliah $schedule, array $data, string $reason): JadwalKuliah
    {
        if ($schedule->status === 'final' && blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Perubahan jadwal final wajib memiliki alasan.']);
        }

        $old = $schedule->toArray();
        $schedule->fill($data);
        $schedule->updated_by = Auth::id();
        $schedule->status = $schedule->status === 'final' ? 'rescheduled' : $schedule->status;
        $schedule->save();

        JadwalHistory::create([
            'tenant_id' => $schedule->tenant_id,
            'jadwal_kuliah_id' => $schedule->id,
            'old_day_of_week' => $old['day_of_week'] ?? null,
            'old_starts_at' => $old['starts_at'] ?? null,
            'old_ends_at' => $old['ends_at'] ?? null,
            'old_ruangan_id' => $old['ruangan_id'] ?? null,
            'new_day_of_week' => $schedule->day_of_week,
            'new_starts_at' => $schedule->starts_at,
            'new_ends_at' => $schedule->ends_at,
            'new_ruangan_id' => $schedule->ruangan_id,
            'reason' => $reason,
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        $this->conflicts->detect($schedule);
        $this->audit->record('SCHEDULE_CHANGED', 'M4', $schedule, $old, $schedule->fresh()->toArray());
        $this->notifyScheduleChanged($schedule, $reason);

        return $schedule->fresh();
    }

    private function notifyScheduleChanged(JadwalKuliah $schedule, string $reason): void
    {
        if ($schedule->dosen?->user_id) {
            $this->notifications->create(
                $schedule->dosen->user_id,
                'SCHEDULE_CHANGED',
                'Jadwal Kuliah Berubah',
                'Jadwal ' . ($schedule->mataKuliah?->name ?? 'kuliah') . ' berubah. ' . $reason,
                reference: $schedule
            );
        }
    }
}
