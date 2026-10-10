<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JadwalKuliahResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\JadwalKuliah;
use App\Services\Sifak\ScheduleConflictService;
use App\Services\Sifak\ScheduleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JadwalKuliahResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = JadwalKuliah::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'M4 KRS & Penjadwalan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload(),
            Forms\Components\Select::make('kelas_kuliah_id')->relationship('kelasKuliah', 'kode_kelas')->searchable()->preload(),
            Forms\Components\Select::make('mata_kuliah_id')->relationship('mataKuliah', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('dosen_id')->relationship('dosen', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('ruangan_id')->relationship('ruangan', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('academic_year')->label('Tahun akademik')->required()->default(date('Y') . '/' . (date('Y') + 1)),
            Forms\Components\Select::make('term')->required()->default('ganjil')->options(['ganjil' => 'Ganjil', 'genap' => 'Genap', 'pendek' => 'Pendek']),
            Forms\Components\Select::make('day_of_week')->label('Hari')->required()->options([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu']),
            Forms\Components\TimePicker::make('starts_at')->label('Mulai')->required()->seconds(false),
            Forms\Components\TimePicker::make('ends_at')->label('Selesai')->required()->seconds(false),
            Forms\Components\TextInput::make('minggu_mulai')->numeric(),
            Forms\Components\TextInput::make('minggu_selesai')->numeric(),
            Forms\Components\Select::make('mode')->required()->default('onsite')->options(['onsite' => 'Onsite', 'online' => 'Online', 'hybrid' => 'Hybrid']),
            Forms\Components\Select::make('status')->required()->default('draft')->options(['draft' => 'Draft', 'planned' => 'Direncanakan', 'published' => 'Dipublikasi', 'final' => 'Final', 'rescheduled' => 'Rescheduled', 'cancelled' => 'Dibatalkan']),
            Forms\Components\TextInput::make('conflict_status')->disabled()->dehydrated(false),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mataKuliah.code')->label('MK')->badge(),
                Tables\Columns\TextColumn::make('mataKuliah.name')->label('Mata kuliah')->searchable(),
                Tables\Columns\TextColumn::make('kelasKuliah.kode_kelas')->label('Kelas')->badge(),
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable(),
                Tables\Columns\TextColumn::make('ruangan.code')->label('Ruang')->badge(),
                Tables\Columns\TextColumn::make('day_of_week')->label('Hari'),
                Tables\Columns\TextColumn::make('starts_at')->label('Mulai'),
                Tables\Columns\TextColumn::make('ends_at')->label('Selesai'),
                Tables\Columns\TextColumn::make('conflict_status')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('checkConflict')
                    ->label('Cek Konflik')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->action(fn (JadwalKuliah $record) => app(ScheduleConflictService::class)->detect($record)),
                Tables\Actions\Action::make('finalize')
                    ->label('Final')
                    ->icon('heroicon-o-lock-closed')
                    ->visible(fn (JadwalKuliah $record) => $record->status !== 'final' && auth()->user()?->can('finalize_schedule'))
                    ->action(fn (JadwalKuliah $record) => app(ScheduleService::class)->finalize($record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJadwalKuliahs::route('/'),
            'create' => Pages\CreateJadwalKuliah::route('/create'),
            'edit' => Pages\EditJadwalKuliah::route('/{record}/edit'),
        ];
    }
}
