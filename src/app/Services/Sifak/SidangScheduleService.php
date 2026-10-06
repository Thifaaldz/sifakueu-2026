<?php

namespace App\Services\Sifak;

use App\Models\SidangRegistration;
use App\Models\SidangSchedule;
use App\Models\SidangScheduleHistory;
use App\Services\Shared\Audit\AuditService;
use App\Services\Shared\Notification\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SidangScheduleService
{
    public function __construct(
        private readonly SidangConflictService $conflicts,
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
    ) {}

    public function createDraft(SidangRegistration $registration, array $data): SidangSchedule
    {
        if (! in_array($registration->status, ['verified', 'ready_for_plotting', 'scheduled'], true)) {
            throw ValidationException::withMessages(['registration' => 'Sidang hanya dapat dijadwalkan setelah pendaftaran terverifikasi.']);
        }

        $conflicts = $this->conflicts->check($registration, $data['tanggal'], $data['jam_mulai'], $data['jam_selesai'], $data['ruangan_id'] ?? null);

        $schedule = SidangSchedule::create([
            'tenant_id' => $registration->tenant_id,
            'sidang_registration_id' => $registration->id,
            'tanggal' => $data['tanggal'],
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'ruangan_id' => $data['ruangan_id'] ?? null,
            'meeting_url' => $data['meeting_url'] ?? null,
            'mode' => $data['mode'] ?? 'onsite',
            'status' => $this->conflicts->hasHardConflict($conflicts) ? 'conflict' : 'validated',
            'created_by' => Auth::id(),
            'conflict_payload' => $conflicts,
        ]);

        $this->audit->record('SIDANG_SCHEDULE_DRAFTED', 'M1', $schedule, [], $schedule->toArray());

        return $schedule;
    }

    public function finalize(SidangSchedule $schedule): SidangSchedule
    {
        $registration = $schedule->registration;
        $conflicts = $this->conflicts->check($registration, $schedule->tanggal->toDateString(), $schedule->jam_mulai, $schedule->jam_selesai, $schedule->ruangan_id, $schedule->id);

        if ($this->conflicts->hasHardConflict($conflicts)) {
            $schedule->update(['status' => 'conflict', 'conflict_payload' => $conflicts]);
            throw ValidationException::withMessages(['schedule' => 'Jadwal belum dapat difinalkan karena masih ada konflik.']);
        }

        $old = $schedule->toArray();
        $schedule->update([
            'status' => 'final',
            'finalized_by' => Auth::id(),
            'conflict_payload' => [],
        ]);
        $registration->update(['status' => 'scheduled']);
        $this->notifyParticipants($schedule);
        $this->audit->record('SIDANG_SCHEDULE_FINALIZED', 'M1', $schedule, $old, $schedule->fresh()->toArray());

        return $schedule->fresh();
    }

    public function reschedule(SidangSchedule $schedule, array $data, string $reason): SidangSchedule
    {
        $old = $schedule->toArray();

        SidangScheduleHistory::create([
            'tenant_id' => $schedule->tenant_id,
            'sidang_schedule_id' => $schedule->id,
            'old_date' => $schedule->tanggal,
            'old_start' => $schedule->jam_mulai,
            'old_end' => $schedule->jam_selesai,
            'old_room_id' => $schedule->ruangan_id,
            'new_date' => $data['tanggal'],
            'new_start' => $data['jam_mulai'],
            'new_end' => $data['jam_selesai'],
            'new_room_id' => $data['ruangan_id'] ?? null,
            'reason' => $reason,
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        $conflicts = $this->conflicts->check($schedule->registration, $data['tanggal'], $data['jam_mulai'], $data['jam_selesai'], $data['ruangan_id'] ?? null, $schedule->id);
        $schedule->update([
            'tanggal' => $data['tanggal'],
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'ruangan_id' => $data['ruangan_id'] ?? null,
            'meeting_url' => $data['meeting_url'] ?? $schedule->meeting_url,
            'mode' => $data['mode'] ?? $schedule->mode,
            'status' => $this->conflicts->hasHardConflict($conflicts) ? 'conflict' : 'validated',
            'conflict_payload' => $conflicts,
        ]);

        $this->audit->record('SIDANG_SCHEDULE_RESCHEDULED', 'M1', $schedule, $old, $schedule->fresh()->toArray());

        return $schedule->fresh();
    }

    public function start(SidangSchedule $schedule): SidangSchedule
    {
        $schedule->update(['status' => 'in_progress', 'started_at' => now(), 'started_by' => Auth::id()]);

        return $schedule->fresh();
    }

    public function complete(SidangSchedule $schedule): SidangSchedule
    {
        $schedule->update(['status' => 'completed', 'completed_at' => now(), 'completed_by' => Auth::id()]);

        return $schedule->fresh();
    }

    private function notifyParticipants(SidangSchedule $schedule): void
    {
        $registration = $schedule->registration;
        $recipients = collect([$registration->mahasiswa?->user_id])
            ->merge($registration->assignments()->with('dosen')->get()->pluck('dosen.user_id'))
            ->filter()
            ->unique();

        foreach ($recipients as $userId) {
            $this->notifications->create(
                $userId,
                'SIDANG_SCHEDULED',
                'Jadwal Sidang Telah Ditetapkan',
                'Sidang dijadwalkan pada ' . $schedule->tanggal?->format('Y-m-d') . ' pukul ' . $schedule->jam_mulai . '.',
                reference: $schedule
            );
        }
    }
}
