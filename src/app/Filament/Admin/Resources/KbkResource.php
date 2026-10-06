<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KbkResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Kbk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KbkResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Kbk::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Kode KBK')->required()->maxLength(30)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->label('Nama KBK')->required()->maxLength(150),
            Forms\Components\TextInput::make('field')->label('Bidang')->maxLength(255),
            Forms\Components\Select::make('ketua_dosen_id')->label('Ketua KBK')->relationship('ketua', 'name')->searchable()->preload(),
            Forms\Components\Select::make('status')->required()->default('active')->options(['active' => 'Aktif', 'inactive' => 'Tidak aktif']),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('KBK')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('field')->label('Bidang')->searchable(),
                Tables\Columns\TextColumn::make('ketua.name')->label('Ketua')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKbks::route('/'),
            'create' => Pages\CreateKbk::route('/create'),
            'edit' => Pages\EditKbk::route('/{record}/edit'),
        ];
    }
}
