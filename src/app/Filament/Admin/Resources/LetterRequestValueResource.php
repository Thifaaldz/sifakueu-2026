<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LetterRequestValueResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterRequestValue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterRequestValueResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = LetterRequestValue::class;
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Data Form Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('surat_id')->relationship('surat', 'subject')->searchable()->preload()->required(),
            Forms\Components\Select::make('field_id')->relationship('field', 'label')->label('Field')->searchable()->preload(),
            Forms\Components\Textarea::make('value_text')->label('Value Text')->columnSpanFull(),
            Forms\Components\Textarea::make('value_json')
                ->label('Value JSON')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                ->dehydrateStateUsing(fn ($state) => filled($state) ? json_decode($state, true) : null)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('surat.request_number')->label('Pengajuan')->searchable(),
            Tables\Columns\TextColumn::make('surat.subject')->label('Perihal')->limit(35),
            Tables\Columns\TextColumn::make('field.label')->label('Field')->searchable(),
            Tables\Columns\TextColumn::make('value_text')->label('Value')->limit(45),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLetterRequestValues::route('/'), 'create' => Pages\CreateLetterRequestValue::route('/create'), 'edit' => Pages\EditLetterRequestValue::route('/{record}/edit')];
    }
}
