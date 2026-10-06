<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PendaftaranSidangResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\PendaftaranSidang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PendaftaranSidangResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = PendaftaranSidang::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'M1 Sidang';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Pendaftaran')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
                    Forms\Components\Select::make('type')->label('Jenis sidang')->required()->options(['sempro' => 'Sempro', 'ta' => 'Tugas Akhir']),
                    Forms\Components\Select::make('pembimbing_id')->relationship('pembimbing', 'name')->searchable()->preload(),
                    Forms\Components\Select::make('penguji_1_id')->relationship('penguji1', 'name')->searchable()->preload()->different('pembimbing_id'),
                    Forms\Components\Select::make('penguji_2_id')->relationship('penguji2', 'name')->searchable()->preload()->different('pembimbing_id')->different('penguji_1_id'),
                    Forms\Components\Select::make('status')->required()->default('submitted')->options([
                        'submitted' => 'Diajukan',
                        'verified' => 'Terverifikasi',
                        'scheduled' => 'Terjadwal',
                        'completed' => 'Selesai',
                        'revision' => 'Revisi',
                        'rejected' => 'Ditolak',
                    ]),
                ]),
            Forms\Components\Section::make('Jadwal dan Hasil')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('ruangan_id')->relationship('ruangan', 'name')->searchable()->preload(),
                    Forms\Components\DatePicker::make('scheduled_date')->label('Tanggal sidang'),
                    Forms\Components\TimePicker::make('starts_at')->label('Mulai')->seconds(false),
                    Forms\Components\TimePicker::make('ends_at')->label('Selesai')->seconds(false),
                    Forms\Components\TextInput::make('score')->label('Nilai')->numeric()->minValue(0)->maxValue(100),
                    Forms\Components\Select::make('result')->label('Hasil')->options(['passed' => 'Lulus', 'revision' => 'Revisi', 'failed' => 'Tidak lulus']),
                    Forms\Components\Textarea::make('revision_notes')->label('Catatan revisi')->columnSpanFull(),
                    Forms\Components\KeyValue::make('validation_payload')->label('Validasi syarat')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('pembimbing.name')->label('Pembimbing'),
                Tables\Columns\TextColumn::make('scheduled_date')->label('Tanggal')->date(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('result')->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendaftaranSidangs::route('/'),
            'create' => Pages\CreatePendaftaranSidang::route('/create'),
            'edit' => Pages\EditPendaftaranSidang::route('/{record}/edit'),
        ];
    }
}
