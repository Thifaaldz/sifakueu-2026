<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LetterAttachmentResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterAttachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterAttachmentResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = LetterAttachment::class;
    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Lampiran Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('surat_id')->relationship('surat', 'subject')->searchable()->preload()->required(),
            Forms\Components\Select::make('stored_file_id')->relationship('storedFile', 'original_name')->label('File')->searchable()->preload(),
            Forms\Components\TextInput::make('attachment_type')->label('Tipe')->default('SUPPORTING')->maxLength(60),
            Forms\Components\Select::make('uploaded_by')->relationship('uploader', 'name')->label('Uploader')->searchable()->preload(),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('surat.request_number')->label('Pengajuan')->searchable(),
            Tables\Columns\TextColumn::make('surat.subject')->label('Perihal')->limit(35),
            Tables\Columns\TextColumn::make('storedFile.original_name')->label('File')->placeholder('-'),
            Tables\Columns\TextColumn::make('attachment_type')->label('Tipe')->badge(),
            Tables\Columns\TextColumn::make('uploader.name')->label('Uploader')->placeholder('-'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLetterAttachments::route('/'), 'create' => Pages\CreateLetterAttachment::route('/create'), 'edit' => Pages\EditLetterAttachment::route('/{record}/edit')];
    }
}
