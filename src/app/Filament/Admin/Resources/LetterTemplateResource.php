<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LetterTemplateResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterTemplateResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = LetterTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Template Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('jenis_surat_id')->relationship('jenisSurat', 'name')->label('Jenis Surat')->searchable()->preload(),
            Forms\Components\TextInput::make('code')->label('Kode')->required()->maxLength(80),
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
            Forms\Components\Select::make('template_format')->label('Format')->default('HTML')->options(['HTML' => 'HTML', 'DOCX' => 'DOCX']),
            Forms\Components\TextInput::make('version')->label('Versi')->default('1.0')->maxLength(30),
            Forms\Components\Toggle::make('active')->label('Aktif')->default(true),
            Forms\Components\RichEditor::make('content')->label('Konten')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable(),
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
            Tables\Columns\TextColumn::make('jenisSurat.name')->label('Jenis')->placeholder('Global'),
            Tables\Columns\TextColumn::make('template_format')->label('Format')->badge(),
            Tables\Columns\TextColumn::make('version')->label('Versi'),
            Tables\Columns\IconColumn::make('active')->label('Aktif')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLetterTemplates::route('/'), 'create' => Pages\CreateLetterTemplate::route('/create'), 'edit' => Pages\EditLetterTemplate::route('/{record}/edit')];
    }
}
