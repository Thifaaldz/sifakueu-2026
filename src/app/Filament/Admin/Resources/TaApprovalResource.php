<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaApprovalResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\TaApproval;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaApprovalResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = TaApproval::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'M7 Dokumen TA';
    protected static ?int $navigationSort = 7;

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('document.tugasAkhir.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('document.section.code')->label('Bagian')->badge(),
            Tables\Columns\TextColumn::make('approver.name')->searchable(),
            Tables\Columns\TextColumn::make('approval_type')->badge(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('approved_at')->dateTime(),
        ])->actions([])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListTaApprovals::route('/')];
    }
}
