<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PeriodeKrsResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\PeriodeKrs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PeriodeKrsResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = PeriodeKrs::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'M4 KRS & Penjadwalan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload()->required(),
            Forms\Components\Select::make('program_studi_id')->relationship('programStudi', 'name')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('tanggal_mulai')->required(),
            Forms\Components\DateTimePicker::make('tanggal_selesai')->required()->after('tanggal_mulai'),
            Forms\Components\DateTimePicker::make('tanggal_revisi_mulai'),
            Forms\Components\DateTimePicker::make('tanggal_revisi_selesai')->after('tanggal_revisi_mulai'),
            Forms\Components\Select::make('status')->required()->default('draft')->options([
                'draft' => 'Draft',
                'open' => 'Open',
                'closed' => 'Closed',
            ]),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('semester.code')->label('Semester')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('programStudi.name')->label('Prodi')->placeholder('Semua Prodi')->searchable(),
            Tables\Columns\TextColumn::make('tanggal_mulai')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('tanggal_selesai')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriodeKrs::route('/'),
            'create' => Pages\CreatePeriodeKrs::route('/create'),
            'edit' => Pages\EditPeriodeKrs::route('/{record}/edit'),
        ];
    }
}
