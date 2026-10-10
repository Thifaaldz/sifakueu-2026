<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangScheduleResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SidangSchedule;
use App\Services\Sifak\SidangScheduleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SidangScheduleResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SidangSchedule::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'M1 Sidang';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sidang_registration_id')->relationship('registration', 'registration_number')->searchable()->preload()->required(),
            Forms\Components\DatePicker::make('tanggal')->required(),
            Forms\Components\TimePicker::make('jam_mulai')->seconds(false)->required(),
            Forms\Components\TimePicker::make('jam_selesai')->seconds(false)->required(),
            Forms\Components\Select::make('ruangan_id')->relationship('ruangan', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('meeting_url')->url(),
            Forms\Components\Select::make('mode')->default('onsite')->options(['onsite' => 'Onsite', 'online' => 'Online', 'hybrid' => 'Hybrid'])->required(),
            Forms\Components\Select::make('status')->default('draft')->options([
                'draft' => 'Draft',
                'conflict' => 'Conflict',
                'validated' => 'Validated',
                'final' => 'Final',
                'rescheduled' => 'Rescheduled',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ]),
            Forms\Components\Placeholder::make('conflict_summary')
                ->label('Konflik terdeteksi')
                ->content(fn (?SidangSchedule $record) => collect($record?->conflict_payload ?? [])
                    ->map(fn ($conflict) => match ($conflict['type'] ?? null) {
                        'ROOM_CONFLICT' => 'Ruangan sudah dipakai sidang lain',
                        'STUDENT_CONFLICT' => 'Mahasiswa sudah memiliki jadwal sidang lain',
                        'EXAMINER_CONFLICT' => 'Dosen penguji/pembimbing sudah menguji sidang lain',
                        'ACADEMIC_SCHEDULE_CONFLICT' => 'Dosen sedang mengajar (jadwal kuliah M4)',
                        default => (string) ($conflict['type'] ?? 'Konflik'),
                    })
                    ->unique()
                    ->implode('; ') ?: 'Tidak ada konflik')
                ->visible(fn (?SidangSchedule $record) => filled($record))
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('registration.registration_number')->searchable(),
            Tables\Columns\TextColumn::make('registration.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('tanggal')->date()->sortable(),
            Tables\Columns\TextColumn::make('jam_mulai')->label('Mulai'),
            Tables\Columns\TextColumn::make('jam_selesai')->label('Selesai'),
            Tables\Columns\TextColumn::make('ruangan.name')->label('Ruang'),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([
            Tables\Actions\Action::make('finalize')
                ->label('Finalisasi')
                ->icon('heroicon-o-lock-closed')
                ->visible(fn (SidangSchedule $record) => in_array($record->status, ['draft', 'validated', 'conflict', 'rescheduled'], true) && (auth()->user()?->can('finalize_sidang_schedule') || auth()->user()?->can('update_sidang::schedule')))
                ->action(fn (SidangSchedule $record) => app(SidangScheduleService::class)->finalize($record)),
            Tables\Actions\Action::make('start')
                ->label('Mulai')
                ->icon('heroicon-o-play')
                ->visible(fn (SidangSchedule $record) => $record->status === 'final')
                ->action(fn (SidangSchedule $record) => app(SidangScheduleService::class)->start($record)),
            Tables\Actions\Action::make('complete')
                ->label('Selesai')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (SidangSchedule $record) => $record->status === 'in_progress')
                ->action(fn (SidangSchedule $record) => app(SidangScheduleService::class)->complete($record)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSidangSchedules::route('/'),
            'create' => Pages\CreateSidangSchedule::route('/create'),
            'edit' => Pages\EditSidangSchedule::route('/{record}/edit'),
        ];
    }
}
