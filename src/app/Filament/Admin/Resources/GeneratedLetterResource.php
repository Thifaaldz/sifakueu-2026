<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GeneratedLetterResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\GeneratedLetter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GeneratedLetterResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = GeneratedLetter::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Dokumen Generated';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('surat_id')->relationship('surat', 'subject')->searchable()->preload()->required(),
            Forms\Components\Select::make('letter_number_id')->relationship('letterNumber', 'formatted_number')->label('Nomor')->searchable()->preload(),
            Forms\Components\Select::make('letter_template_id')->relationship('template', 'name')->label('Template')->searchable()->preload(),
            Forms\Components\TextInput::make('template_version')->label('Versi Template'),
            Forms\Components\TextInput::make('html_path')->label('HTML Path')->columnSpanFull(),
            Forms\Components\TextInput::make('checksum')->columnSpanFull(),
            Forms\Components\DateTimePicker::make('generated_at')->label('Generated'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('surat.number')->label('Nomor')->searchable()->placeholder('-'),
            Tables\Columns\TextColumn::make('surat.subject')->label('Perihal')->limit(35)->searchable(),
            Tables\Columns\TextColumn::make('template.name')->label('Template')->placeholder('-'),
            Tables\Columns\TextColumn::make('template_version')->label('Versi'),
            Tables\Columns\TextColumn::make('checksum')->limit(18)->copyable(),
            Tables\Columns\TextColumn::make('generated_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListGeneratedLetters::route('/'), 'create' => Pages\CreateGeneratedLetter::route('/create'), 'edit' => Pages\EditGeneratedLetter::route('/{record}/edit')];
    }
}
