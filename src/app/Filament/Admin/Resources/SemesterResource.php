<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SemesterResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Semester;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SemesterResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Semester::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tahun_akademik_id')->relationship('tahunAkademik', 'code')->searchable()->preload()->required(),
            Forms\Components\Select::make('name')
                ->label('Nama semester')
                ->required()
                ->options(['Ganjil' => 'Ganjil', 'Genap' => 'Genap', 'Pendek' => 'Pendek']),
            Forms\Components\TextInput::make('code')->label('Kode semester')->required()->maxLength(20)->unique(ignoreRecord: true),
            Forms\Components\DatePicker::make('starts_on')->label('Tanggal mulai')->required(),
            Forms\Components\DatePicker::make('ends_on')->label('Tanggal selesai')->required()->after('starts_on'),
            Forms\Components\Select::make('status')
                ->required()
                ->default('planned')
                ->options(['planned' => 'Direncanakan', 'active' => 'Aktif', 'closed' => 'Ditutup']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tahunAkademik.code')->label('Tahun')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Semester')->badge()->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('starts_on')->label('Mulai')->date()->sortable(),
                Tables\Columns\TextColumn::make('ends_on')->label('Selesai')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSemesters::route('/'),
            'create' => Pages\CreateSemester::route('/create'),
            'edit' => Pages\EditSemester::route('/{record}/edit'),
        ];
    }
}
