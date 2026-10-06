<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KelasKuliahResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\KelasKuliah;
use App\Services\Sifak\LecturerPlottingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KelasKuliahResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = KelasKuliah::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'M4 KRS & Penjadwalan';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('penawaran_mata_kuliah_id')->relationship('penawaranMataKuliah', 'id')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('kode_kelas')->required()->maxLength(60),
            Forms\Components\TextInput::make('kapasitas')->numeric()->required()->default(35),
            Forms\Components\TextInput::make('jumlah_peserta')->numeric()->default(0),
            Forms\Components\Select::make('status')->required()->default('draft')->options([
                'draft' => 'Draft',
                'warning' => 'Warning',
                'plotting' => 'Plotting',
                'scheduled' => 'Scheduled',
                'final' => 'Final',
                'cancelled' => 'Cancelled',
            ]),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('penawaranMataKuliah.mataKuliah.code')->label('MK')->badge(),
            Tables\Columns\TextColumn::make('penawaranMataKuliah.mataKuliah.name')->label('Mata Kuliah')->searchable(),
            Tables\Columns\TextColumn::make('kode_kelas')->badge()->searchable(),
            Tables\Columns\TextColumn::make('kapasitas')->sortable(),
            Tables\Columns\TextColumn::make('jumlah_peserta')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([
            Tables\Actions\Action::make('recommend')
                ->label('M5 Kandidat')
                ->icon('heroicon-o-trophy')
                ->action(fn (KelasKuliah $record) => app(LecturerPlottingService::class)->requestRecommendations($record)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelasKuliahs::route('/'),
            'create' => Pages\CreateKelasKuliah::route('/create'),
            'edit' => Pages\EditKelasKuliah::route('/{record}/edit'),
        ];
    }
}
