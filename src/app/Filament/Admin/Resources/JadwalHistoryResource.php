<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JadwalHistoryResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\JadwalHistory;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JadwalHistoryResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = JadwalHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'M4 KRS & Penjadwalan';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('jadwalKuliah.mataKuliah.code')->label('MK')->badge(),
            Tables\Columns\TextColumn::make('old_day_of_week')->label('Hari Lama'),
            Tables\Columns\TextColumn::make('old_starts_at')->label('Mulai Lama'),
            Tables\Columns\TextColumn::make('new_day_of_week')->label('Hari Baru'),
            Tables\Columns\TextColumn::make('new_starts_at')->label('Mulai Baru'),
            Tables\Columns\TextColumn::make('reason')->limit(80),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ])->actions([])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJadwalHistories::route('/'),
        ];
    }
}
