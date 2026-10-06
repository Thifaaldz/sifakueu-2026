<?php

namespace App\Services\Sifak;

use App\Models\JadwalKuliah;
use App\Models\SidangRegistration;
use App\Models\SidangSchedule;
use Illuminate\Support\Collection;

class SidangConflictService
{
    public function check(SidangRegistration $registration, string $date, string $start, string $end, ?int $roomId = null, ?int $ignoreScheduleId = null): array
    {
        $conflicts = collect();
        $dosenIds = $registration->assignments()->pluck('dosen_id')->all();

        SidangSchedule::query()
            ->where('tanggal', $date)
            ->whereIn('status', ['validated', 'final'])
            ->when($ignoreScheduleId, fn ($query) => $query->where('id', '!=', $ignoreScheduleId))
            ->where(function ($query) use ($start, $end) {
                $query->where('jam_mulai', '<', $end)->where('jam_selesai', '>', $start);
            })
            ->with('registration.assignments')
            ->get()
            ->each(function (SidangSchedule $schedule) use ($conflicts, $registration, $dosenIds, $roomId) {
                if ($roomId && $schedule->ruangan_id === $roomId) {
                    $conflicts->push(['type' => 'ROOM_CONFLICT', 'schedule_id' => $schedule->id]);
                }

                if ($schedule->registration?->mahasiswa_id === $registration->mahasiswa_id) {
                    $conflicts->push(['type' => 'STUDENT_CONFLICT', 'schedule_id' => $schedule->id]);
                }

                $overlappedDosen = array_intersect($dosenIds, $schedule->registration?->assignments?->pluck('dosen_id')->all() ?? []);

                foreach ($overlappedDosen as $dosenId) {
                    $conflicts->push(['type' => 'EXAMINER_CONFLICT', 'dosen_id' => $dosenId, 'schedule_id' => $schedule->id]);
                }
            });

        $dayOfWeek = (int) date('N', strtotime($date));
        JadwalKuliah::query()
            ->where('day_of_week', $dayOfWeek)
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->whereIn('dosen_id', $dosenIds)
            ->get()
            ->each(fn (JadwalKuliah $jadwal) => $conflicts->push(['type' => 'ACADEMIC_SCHEDULE_CONFLICT', 'dosen_id' => $jadwal->dosen_id, 'jadwal_kuliah_id' => $jadwal->id]));

        return $conflicts->values()->all();
    }

    public function hasHardConflict(array|Collection $conflicts): bool
    {
        return collect($conflicts)->isNotEmpty();
    }
}
