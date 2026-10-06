<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PlottingDosenResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\PlottingDosen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlottingDosenResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = PlottingDosen::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'M4 KRS & Penjadwalan';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('kelas_kuliah_id')->relationship('kelasKuliah', 'kode_kelas')->searchable()->preload()->required(),
            Forms\Components\Select::make('dosen_id')->relationship('dosen', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('rekomendasi_pengampu_id')->relationship('rekomendasiPengampu', 'id')->searchable()->preload(),
            Forms\Components\Select::make('role_pengampu')->required()->default('utama')->options([
                'utama' => 'Utama',
                'team_teaching' => 'Team Teaching',
                'assistant' => 'Assistant',
            ]),
            Forms\Components\TextInput::make('sks_beban')->numeric()->required()->default(0),
            Forms\Components\Select::make('status')->required()->default('draft')->options([
                'draft' => 'Draft',
                'assigned' => 'Assigned',
                'approved' => 'Approved',
                'cancelled' => 'Cancelled',
            ]),
            Forms\Components\Textarea::make('justification')->label('Justifikasi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('kelasKuliah.kode_kelas')->label('Kelas')->badge()->searchable(),
            Tables\Columns\TextColumn::make('kelasKuliah.penawaranMataKuliah.mataKuliah.name')->label('Mata Kuliah')->searchable(),
            Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable(),
            Tables\Columns\TextColumn::make('role_pengampu')->badge(),
            Tables\Columns\TextColumn::make('sks_beban')->label('SKS'),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlottingDosens::route('/'),
            'create' => Pages\CreatePlottingDosen::route('/create'),
            'edit' => Pages\EditPlottingDosen::route('/{record}/edit'),
        ];
    }
}
