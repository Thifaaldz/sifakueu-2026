<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProgramStudiResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\ProgramStudi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProgramStudiResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = ProgramStudi::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('fakultas_id')->relationship('fakultas', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('code')->label('Kode')->required()->maxLength(32)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->label('Nama prodi')->required()->maxLength(150),
            Forms\Components\Select::make('degree')
                ->label('Jenjang')
                ->required()
                ->options(['D3' => 'D3', 'D4' => 'D4', 'S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3']),
            Forms\Components\Select::make('kaprodi_dosen_id')->label('Kaprodi')->relationship('kaprodi', 'name')->searchable()->preload(),
            Forms\Components\Select::make('status')
                ->required()
                ->default('active')
                ->options(['active' => 'Aktif', 'inactive' => 'Tidak aktif']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama prodi')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fakultas.short_name')->label('Fakultas')->sortable(),
                Tables\Columns\TextColumn::make('degree')->label('Jenjang')->searchable(),
                Tables\Columns\TextColumn::make('kaprodi.name')->label('Kaprodi')->toggleable(),
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
            'index' => Pages\ListProgramStudis::route('/'),
            'create' => Pages\CreateProgramStudi::route('/create'),
            'edit' => Pages\EditProgramStudi::route('/{record}/edit'),
        ];
    }
}
