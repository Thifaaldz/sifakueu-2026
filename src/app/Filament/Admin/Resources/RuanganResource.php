<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RuanganResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Ruangan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RuanganResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Ruangan::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Kode')->required()->maxLength(32)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->label('Nama ruang')->required()->maxLength(100),
            Forms\Components\TextInput::make('capacity')->label('Kapasitas')->numeric()->required()->minValue(1),
            Forms\Components\Select::make('type')->required()->default('kelas')->options([
                'kelas' => 'Kelas',
                'lab' => 'Laboratorium',
                'sidang' => 'Ruang sidang',
                'lainnya' => 'Lainnya',
            ]),
            Forms\Components\TextInput::make('building')->label('Gedung')->maxLength(100),
            Forms\Components\TextInput::make('floor')->label('Lantai')->maxLength(20),
            Forms\Components\TextInput::make('location')->label('Lokasi')->maxLength(255),
            Forms\Components\Select::make('status')->required()->default('active')->options([
                'active' => 'Aktif',
                'maintenance' => 'Maintenance',
                'inactive' => 'Tidak aktif',
            ]),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('capacity')->label('Kapasitas')->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('building')->label('Gedung')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('floor')->label('Lantai')->toggleable(),
                Tables\Columns\TextColumn::make('location')->label('Lokasi')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRuangans::route('/'),
            'create' => Pages\CreateRuangan::route('/create'),
            'edit' => Pages\EditRuangan::route('/{record}/edit'),
        ];
    }
}
