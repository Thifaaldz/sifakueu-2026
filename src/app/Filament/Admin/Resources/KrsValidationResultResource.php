<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KrsValidationResultResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\KrsValidationResult;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KrsValidationResultResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = KrsValidationResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'M4 KRS & Penjadwalan';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('krs.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('validation_code')->label('Kode')->badge()->searchable(),
            Tables\Columns\TextColumn::make('severity')->badge(),
            Tables\Columns\IconColumn::make('passed')->boolean(),
            Tables\Columns\TextColumn::make('message')->limit(80),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ])->actions([])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKrsValidationResults::route('/'),
        ];
    }
}
