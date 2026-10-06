<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RevisionCycleResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\RevisionCycle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RevisionCycleResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = RevisionCycle::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'M7 Dokumen TA';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tugas_akhir_id')
                ->relationship('tugasAkhir', 'judul', modifyQueryUsing: fn ($query) => self::scopeTugasAkhirOptions($query))
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('source')->default('supervisor')->options(['supervisor' => 'Supervisor', 'sempro' => 'Sempro', 'sidang_ta' => 'Sidang TA', 'admin' => 'Admin']),
            Forms\Components\DateTimePicker::make('started_at'),
            Forms\Components\DateTimePicker::make('deadline'),
            Forms\Components\Select::make('status')->default('open')->options(['open' => 'Open', 'closed' => 'Closed', 'cancelled' => 'Cancelled']),
            Forms\Components\DateTimePicker::make('closed_at'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tugasAkhir.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('source')->badge(),
            Tables\Columns\TextColumn::make('deadline')->dateTime(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevisionCycles::route('/'),
            'create' => Pages\CreateRevisionCycle::route('/create'),
            'edit' => Pages\EditRevisionCycle::route('/{record}/edit'),
        ];
    }

    private static function scopeTugasAkhirOptions($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('mahasiswa')) {
            return $query->whereHas('mahasiswa', fn ($builder) => $builder->where('user_id', $user->id));
        }

        if ($user?->hasAnyRole(['dosen', 'dosen_pembimbing', 'dosen_pa', 'dosen_penguji'])) {
            $dosenId = $user->dosen?->id;

            return $dosenId
                ? $query->where(fn ($builder) => $builder->where('pembimbing_1_id', $dosenId)->orWhere('pembimbing_2_id', $dosenId))
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
