<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KurikulumResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Kurikulum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KurikulumResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Kurikulum::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Kurikulum')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('program_studi_id')->relationship('programStudi', 'name')->searchable()->preload()->required(),
                    Forms\Components\TextInput::make('code')->label('Kode kurikulum')->required()->maxLength(50)->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('name')->label('Nama kurikulum')->required()->maxLength(150),
                    Forms\Components\TextInput::make('start_year')->label('Tahun mulai')->numeric()->required()->minValue(2000)->maxValue(2100),
                    Forms\Components\TextInput::make('end_year')->label('Tahun selesai')->numeric()->minValue(2000)->maxValue(2100),
                    Forms\Components\TextInput::make('total_sks')->label('Total SKS')->numeric()->required()->minValue(1)->default(144),
                    Forms\Components\Select::make('status')
                        ->required()
                        ->default('draft')
                        ->options(['draft' => 'Draft', 'active' => 'Aktif', 'inactive' => 'Tidak aktif']),
                    Forms\Components\Toggle::make('is_active')->label('Dipakai aktif')->default(false),
                ]),
            Forms\Components\Section::make('Mata Kuliah Kurikulum')
                ->schema([
                    Forms\Components\Select::make('mataKuliahs')
                        ->label('Mata kuliah')
                        ->relationship('mataKuliahs', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Kurikulum')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('programStudi.name')->label('Prodi')->sortable(),
                Tables\Columns\TextColumn::make('start_year')->label('Mulai')->sortable(),
                Tables\Columns\TextColumn::make('end_year')->label('Selesai')->sortable(),
                Tables\Columns\TextColumn::make('total_sks')->label('SKS')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKurikulums::route('/'),
            'create' => Pages\CreateKurikulum::route('/create'),
            'edit' => Pages\EditKurikulum::route('/{record}/edit'),
        ];
    }
}
