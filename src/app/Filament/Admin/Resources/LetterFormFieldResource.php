<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LetterFormFieldResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterFormField;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterFormFieldResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = LetterFormField::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Form Field Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('jenis_surat_id')->relationship('jenisSurat', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('field_key')->label('Key')->required()->maxLength(80),
            Forms\Components\TextInput::make('label')->required()->maxLength(255),
            Forms\Components\Select::make('field_type')->default('TEXT')->options(['TEXT' => 'Text', 'TEXTAREA' => 'Textarea', 'DATE' => 'Date', 'NUMBER' => 'Number', 'SELECT' => 'Select', 'MULTISELECT' => 'Multi Select', 'CHECKBOX' => 'Checkbox', 'FILE' => 'File']),
            Forms\Components\TextInput::make('validation_rule')->label('Rule')->maxLength(255),
            Forms\Components\TextInput::make('sequence')->label('Urutan')->numeric()->default(1),
            Forms\Components\Toggle::make('required')->label('Wajib')->default(false),
            Forms\Components\Toggle::make('active')->label('Aktif')->default(true),
            Forms\Components\TagsInput::make('options_json')->label('Options')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('jenisSurat.name')->label('Jenis')->searchable(),
            Tables\Columns\TextColumn::make('field_key')->label('Key')->badge()->searchable(),
            Tables\Columns\TextColumn::make('label')->searchable(),
            Tables\Columns\TextColumn::make('field_type')->badge(),
            Tables\Columns\IconColumn::make('required')->boolean(),
            Tables\Columns\TextColumn::make('sequence')->sortable(),
        ])->defaultSort('sequence')->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLetterFormFields::route('/'), 'create' => Pages\CreateLetterFormField::route('/create'), 'edit' => Pages\EditLetterFormField::route('/{record}/edit')];
    }
}
