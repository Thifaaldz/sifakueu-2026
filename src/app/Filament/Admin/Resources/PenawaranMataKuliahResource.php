<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PenawaranMataKuliahResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\PenawaranMataKuliah;
use App\Services\Sifak\ClassFormationService;
use App\Services\Sifak\CourseDemandService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PenawaranMataKuliahResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = PenawaranMataKuliah::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'M4 KRS & Penjadwalan';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload()->required(),
            Forms\Components\Select::make('program_studi_id')->relationship('programStudi', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('mata_kuliah_id')->relationship('mataKuliah', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('kurikulum_id')->relationship('kurikulum', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('kuota_default')->numeric()->default(35)->required(),
            Forms\Components\TextInput::make('minimal_peserta')->numeric()->default(10)->required(),
            Forms\Components\TextInput::make('maksimal_peserta')->numeric()->default(35)->required(),
            Forms\Components\TextInput::make('target_jumlah_kelas')->numeric()->default(1)->required(),
            Forms\Components\TextInput::make('jumlah_peminat')->numeric()->disabled()->dehydrated(false),
            Forms\Components\Select::make('status')->required()->default('draft')->options([
                'draft' => 'Draft',
                'open' => 'Open',
                'closed' => 'Closed',
                'cancelled' => 'Cancelled',
            ]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('semester.code')->label('Semester')->sortable(),
            Tables\Columns\TextColumn::make('programStudi.code')->label('Prodi')->badge(),
            Tables\Columns\TextColumn::make('mataKuliah.code')->label('Kode')->badge(),
            Tables\Columns\TextColumn::make('mataKuliah.name')->label('Mata Kuliah')->searchable(),
            Tables\Columns\TextColumn::make('jumlah_peminat')->label('Peminat')->sortable(),
            Tables\Columns\TextColumn::make('target_jumlah_kelas')->label('Target Kelas')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([
            Tables\Actions\Action::make('refreshDemand')
                ->label('Hitung Peminat')
                ->icon('heroicon-o-calculator')
                ->action(fn (PenawaranMataKuliah $record) => app(CourseDemandService::class)->refresh($record)),
            Tables\Actions\Action::make('generateClasses')
                ->label('Generate Kelas')
                ->icon('heroicon-o-square-3-stack-3d')
                ->action(fn (PenawaranMataKuliah $record) => app(ClassFormationService::class)->generateDraft($record)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenawaranMataKuliahs::route('/'),
            'create' => Pages\CreatePenawaranMataKuliah::route('/create'),
            'edit' => Pages\EditPenawaranMataKuliah::route('/{record}/edit'),
        ];
    }
}
