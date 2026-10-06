<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JadwalConflictResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\JadwalConflict;
use App\Services\Sifak\ScheduleConflictService;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JadwalConflictResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = JadwalConflict::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'M4 KRS & Penjadwalan';

    protected static ?int $navigationSort = 9;

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('jadwalKuliah.mataKuliah.code')->label('MK')->badge(),
            Tables\Columns\TextColumn::make('conflict_type')->badge()->searchable(),
            Tables\Columns\TextColumn::make('severity')->badge(),
            Tables\Columns\TextColumn::make('message')->limit(80),
            Tables\Columns\IconColumn::make('resolved')->boolean(),
            Tables\Columns\TextColumn::make('resolved_at')->dateTime(),
        ])->actions([
            Tables\Actions\Action::make('resolve')
                ->label('Resolve')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (JadwalConflict $record) => ! $record->resolved)
                ->action(fn (JadwalConflict $record) => app(ScheduleConflictService::class)->resolve($record)),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJadwalConflicts::route('/'),
        ];
    }
}
