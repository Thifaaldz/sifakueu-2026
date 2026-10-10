<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KrsDetailResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\KrsDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KrsDetailResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = KrsDetail::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'M4 KRS & Penjadwalan';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('krs_id')
                ->relationship('krs', 'id', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('mahasiswa')
                    ? $query->whereIn('status', ['draft', 'revision_required'])->whereHas('mahasiswa', fn ($builder) => $builder->where('user_id', auth()->id()))
                    : $query)
                ->getOptionLabelFromRecordUsing(fn ($record) => 'KRS #' . $record->id . ' - ' . $record->mahasiswa?->nim . ' (' . $record->semester?->code . ')')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('mata_kuliah_id')->relationship('mataKuliah', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('penawaran_mata_kuliah_id')->relationship('penawaranMataKuliah', 'id')->searchable()->preload(),
            Forms\Components\Select::make('kelas_kuliah_id')->relationship('kelasKuliah', 'kode_kelas')->searchable()->preload(),
            Forms\Components\TextInput::make('sks')->numeric()->required(),
            Forms\Components\Select::make('status')->required()->default('selected')->visible(fn () => ! auth()->user()?->hasRole('mahasiswa'))->options(['selected' => 'Selected', 'approved' => 'Approved', 'dropped' => 'Dropped']),
            Forms\Components\Select::make('validation_status')->required()->default('valid')->visible(fn () => ! auth()->user()?->hasRole('mahasiswa'))->options(['valid' => 'Valid', 'invalid' => 'Invalid', 'warning' => 'Warning']),
            Forms\Components\TextInput::make('final_score')->visible(fn () => ! auth()->user()?->hasRole('mahasiswa'))->label('Nilai Akhir')->numeric()->minValue(0)->maxValue(100),
            Forms\Components\TextInput::make('final_grade')->visible(fn () => ! auth()->user()?->hasRole('mahasiswa'))->label('Grade')->maxLength(10),
            Forms\Components\DateTimePicker::make('passed_at')->visible(fn () => ! auth()->user()?->hasRole('mahasiswa'))->label('Lulus pada'),
            Forms\Components\Textarea::make('validation_note')->visible(fn () => ! auth()->user()?->hasRole('mahasiswa'))->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('krs.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('krs.mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('mataKuliah.code')->label('MK')->badge(),
            Tables\Columns\TextColumn::make('mataKuliah.name')->label('Mata Kuliah')->searchable(),
            Tables\Columns\TextColumn::make('kelasKuliah.kode_kelas')->label('Kelas')->badge(),
            Tables\Columns\TextColumn::make('sks')->label('SKS'),
            Tables\Columns\TextColumn::make('final_score')->label('Nilai')->sortable(),
            Tables\Columns\TextColumn::make('final_grade')->label('Grade')->badge(),
            Tables\Columns\TextColumn::make('validation_status')->badge(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKrsDetails::route('/'),
            'create' => Pages\CreateKrsDetail::route('/create'),
            'edit' => Pages\EditKrsDetail::route('/{record}/edit'),
        ];
    }
}
