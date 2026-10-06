<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaProgressLogResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\TaProgressLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaProgressLogResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = TaProgressLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'M7 Dokumen TA';
    protected static ?int $navigationSort = 8;

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tugasAkhir.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('progress_type')->badge(),
            Tables\Columns\TextColumn::make('old_status')->toggleable(),
            Tables\Columns\TextColumn::make('new_status')->badge(),
            Tables\Columns\TextColumn::make('progress_percent')->suffix('%'),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ])->actions([])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListTaProgressLogs::route('/')];
    }
}
