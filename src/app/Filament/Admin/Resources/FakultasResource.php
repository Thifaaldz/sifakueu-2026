<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FakultasResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Fakultas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FakultasResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Fakultas::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Fakultas')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('code')->label('Kode fakultas')->required()->maxLength(20)->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('name')->label('Nama fakultas')->required()->maxLength(150),
                    Forms\Components\TextInput::make('short_name')->label('Nama singkat')->required()->maxLength(50)->unique(ignoreRecord: true),
                    Forms\Components\Select::make('status')
                        ->required()
                        ->default('active')
                        ->options(['active' => 'Aktif', 'inactive' => 'Tidak aktif']),
                    Forms\Components\Textarea::make('address')->label('Alamat')->columnSpanFull(),
                    Forms\Components\TextInput::make('email')->email()->maxLength(150),
                    Forms\Components\TextInput::make('phone')->label('Telepon')->tel()->maxLength(30),
                    Forms\Components\FileUpload::make('logo_path')->label('Logo')->image()->directory('fakultas/logos'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Fakultas')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('short_name')->label('Singkat')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
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
            'index' => Pages\ListFakultas::route('/'),
            'create' => Pages\CreateFakultas::route('/create'),
            'edit' => Pages\EditFakultas::route('/{record}/edit'),
        ];
    }
}
