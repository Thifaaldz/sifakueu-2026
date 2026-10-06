<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StoredFileResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\StoredFile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StoredFileResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = StoredFile::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Shared Services';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('module')->disabled(),
            Forms\Components\TextInput::make('owner_type')->disabled(),
            Forms\Components\TextInput::make('owner_id')->disabled(),
            Forms\Components\TextInput::make('original_name')->disabled(),
            Forms\Components\TextInput::make('stored_name')->disabled(),
            Forms\Components\TextInput::make('disk')->disabled(),
            Forms\Components\TextInput::make('path')->disabled()->columnSpanFull(),
            Forms\Components\TextInput::make('mime_type')->disabled(),
            Forms\Components\TextInput::make('size')->disabled(),
            Forms\Components\TextInput::make('checksum')->disabled()->columnSpanFull(),
            Forms\Components\TextInput::make('version')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('module')->badge()->searchable(),
                Tables\Columns\TextColumn::make('original_name')->searchable()->limit(35),
                Tables\Columns\TextColumn::make('mime_type')->toggleable(),
                Tables\Columns\TextColumn::make('size')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('version')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('uploader.email')->label('Uploaded by')->toggleable(),
            ])
            ->actions([Tables\Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoredFiles::route('/'),
            'view' => Pages\ViewStoredFile::route('/{record}'),
        ];
    }
}
