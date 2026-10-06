<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TahunAkademikResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\TahunAkademik;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TahunAkademikResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = TahunAkademik::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Kode tahun')->placeholder('2026/2027')->required()->maxLength(20)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('start_year')->label('Tahun mulai')->numeric()->required()->minValue(2000)->maxValue(2100),
            Forms\Components\TextInput::make('end_year')->label('Tahun selesai')->numeric()->required()->minValue(2000)->maxValue(2100)->gt('start_year'),
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
                Tables\Columns\TextColumn::make('code')->label('Tahun akademik')->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_year')->label('Mulai')->sortable(),
                Tables\Columns\TextColumn::make('end_year')->label('Selesai')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTahunAkademiks::route('/'),
            'create' => Pages\CreateTahunAkademik::route('/create'),
            'edit' => Pages\EditTahunAkademik::route('/{record}/edit'),
        ];
    }
}
