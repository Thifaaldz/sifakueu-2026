<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LetterNumberResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterNumber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterNumberResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = LetterNumber::class;
    protected static ?string $navigationIcon = 'heroicon-o-hashtag';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Nomor Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('surat_id')->relationship('surat', 'subject')->searchable()->preload()->required(),
            Forms\Components\Select::make('jenis_surat_id')->relationship('jenisSurat', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('sequence_number')->numeric()->required(),
            Forms\Components\TextInput::make('formatted_number')->label('Nomor')->required(),
            Forms\Components\DateTimePicker::make('generated_at')->label('Generated'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('formatted_number')->label('Nomor')->searchable()->copyable(),
            Tables\Columns\TextColumn::make('surat.subject')->label('Perihal')->limit(35),
            Tables\Columns\TextColumn::make('jenisSurat.code')->label('Jenis')->badge(),
            Tables\Columns\TextColumn::make('sequence_number')->label('Seq')->sortable(),
            Tables\Columns\TextColumn::make('generated_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLetterNumbers::route('/'), 'create' => Pages\CreateLetterNumber::route('/create'), 'edit' => Pages\EditLetterNumber::route('/{record}/edit')];
    }
}
