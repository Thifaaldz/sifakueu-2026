<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MataKuliahResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MataKuliah;
use App\Services\Sifak\DosenRecommendationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MataKuliahResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MataKuliah::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Kode MK')->required()->maxLength(32)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->label('Nama MK')->required()->maxLength(150),
            Forms\Components\Select::make('program_studi_id')->relationship('programStudi', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('rumpun_ilmu_id')->relationship('rumpunIlmu', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('sks')->numeric()->required()->minValue(1)->maxValue(6),
            Forms\Components\TextInput::make('semester')->numeric()->required()->minValue(1)->maxValue(14),
            Forms\Components\Select::make('type')->label('Tipe MK')->required()->default('wajib')->options(['wajib' => 'Wajib', 'pilihan' => 'Pilihan']),
            Forms\Components\Select::make('prerequisites')->label('Prasyarat')->relationship('prerequisites', 'name')->multiple()->searchable()->preload(),
            Forms\Components\Select::make('status')->required()->default('active')->options(['active' => 'Aktif', 'inactive' => 'Tidak aktif']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Mata kuliah')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('programStudi.name')->label('Prodi'),
                Tables\Columns\TextColumn::make('rumpunIlmu.name')->label('Rumpun'),
                Tables\Columns\TextColumn::make('sks')->sortable(),
                Tables\Columns\TextColumn::make('semester')->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Tipe')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('recommend')
                    ->label('Hitung rekomendasi')
                    ->icon('heroicon-o-sparkles')
                    ->action(fn (MataKuliah $record) => app(DosenRecommendationService::class)->refreshForCourse($record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMataKuliahs::route('/'),
            'create' => Pages\CreateMataKuliah::route('/create'),
            'edit' => Pages\EditMataKuliah::route('/{record}/edit'),
        ];
    }
}
