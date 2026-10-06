<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KrsResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Krs;
use App\Services\Sifak\KrsService;
use App\Services\Sifak\KrsValidationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KrsResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Krs::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'M4 KRS & Jadwal';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload(),
            Forms\Components\Select::make('tahun_akademik_id')->relationship('tahunAkademik', 'code')->searchable()->preload(),
            Forms\Components\Select::make('dosen_pa_id')->relationship('dosenPa', 'name')->label('Dosen PA')->searchable()->preload(),
            Forms\Components\Select::make('mata_kuliah_id')->relationship('mataKuliah', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('academic_year')->label('Tahun akademik')->required()->default(date('Y') . '/' . (date('Y') + 1)),
            Forms\Components\Select::make('term')->required()->default('ganjil')->options(['ganjil' => 'Ganjil', 'genap' => 'Genap', 'pendek' => 'Pendek']),
            Forms\Components\Select::make('approved_by')->relationship('approver', 'name')->label('Dosen PA')->searchable()->preload(),
            Forms\Components\TextInput::make('total_sks')->numeric()->default(0),
            Forms\Components\Select::make('status')->required()->default('draft')->options([
                'draft' => 'Draft',
                'submitted' => 'Diajukan',
                'waiting_pa' => 'Menunggu PA',
                'revision_required' => 'Perlu Revisi',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'final' => 'Final',
                'cancelled' => 'Dibatalkan',
            ]),
            Forms\Components\DateTimePicker::make('submitted_at')->label('Diajukan pada'),
            Forms\Components\DateTimePicker::make('approved_at')->label('Disetujui pada'),
            Forms\Components\DateTimePicker::make('finalized_at')->label('Final pada'),
            Forms\Components\Textarea::make('note')->label('Catatan')->columnSpanFull(),
            Forms\Components\KeyValue::make('validation_notes')->label('Catatan validasi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('mataKuliah.code')->label('Kode MK')->badge(),
                Tables\Columns\TextColumn::make('semester.code')->label('Semester')->toggleable(),
                Tables\Columns\TextColumn::make('details_count')->counts('details')->label('MK'),
                Tables\Columns\TextColumn::make('total_sks')->label('SKS')->sortable(),
                Tables\Columns\TextColumn::make('academic_year')->label('TA'),
                Tables\Columns\TextColumn::make('term')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('validate')
                    ->label('Validasi')
                    ->icon('heroicon-o-shield-check')
                    ->action(fn (Krs $record) => app(KrsValidationService::class)->validate($record)),
                Tables\Actions\Action::make('submit')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Krs $record) => in_array($record->status, ['draft', 'revision_required'], true) && auth()->user()?->can('submit_own_krs'))
                    ->action(fn (Krs $record) => app(KrsService::class)->submit($record)),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Krs $record) => $record->status === 'waiting_pa' && auth()->user()?->can('approve_krs'))
                    ->form([Forms\Components\Textarea::make('note')->label('Catatan')])
                    ->action(fn (Krs $record, array $data) => app(KrsService::class)->approve($record, $data['note'] ?? null)),
                Tables\Actions\Action::make('revision')
                    ->label('Minta Revisi')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Krs $record) => $record->status === 'waiting_pa' && auth()->user()?->can('request_krs_revision'))
                    ->form([Forms\Components\Textarea::make('note')->label('Catatan revisi')->required()])
                    ->action(fn (Krs $record, array $data) => app(KrsService::class)->requestRevision($record, $data['note'])),
                Tables\Actions\Action::make('finalize')
                    ->label('Final')
                    ->icon('heroicon-o-lock-closed')
                    ->visible(fn (Krs $record) => $record->status === 'approved' && auth()->user()?->can('update_krs'))
                    ->action(fn (Krs $record) => app(KrsService::class)->finalize($record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKrs::route('/'),
            'create' => Pages\CreateKrs::route('/create'),
            'edit' => Pages\EditKrs::route('/{record}/edit'),
        ];
    }
}
