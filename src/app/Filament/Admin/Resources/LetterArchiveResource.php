<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LetterArchiveResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterArchive;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterArchiveResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = LetterArchive::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Arsip Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('generated_letter_id')->relationship('generatedLetter', 'checksum')->label('Dokumen')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('archive_code')->label('Kode Arsip')->required(),
            Forms\Components\Select::make('classification')->default('PERMANENT')->options(['PERMANENT' => 'Permanent', '5_YEARS' => '5 Tahun', '10_YEARS' => '10 Tahun', 'CUSTOM' => 'Custom']),
            Forms\Components\DatePicker::make('retention_until')->label('Retensi Sampai'),
            Forms\Components\DateTimePicker::make('archived_at')->label('Diarsipkan'),
            Forms\Components\Select::make('archived_by')->relationship('archivedBy', 'name')->searchable()->preload(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('archive_code')->label('Kode Arsip')->searchable()->copyable(),
            Tables\Columns\TextColumn::make('generatedLetter.surat.number')->label('Nomor')->searchable()->placeholder('-'),
            Tables\Columns\TextColumn::make('generatedLetter.surat.subject')->label('Perihal')->limit(35)->searchable(),
            Tables\Columns\TextColumn::make('classification')->badge(),
            Tables\Columns\TextColumn::make('retention_until')->date()->placeholder('-'),
            Tables\Columns\TextColumn::make('archived_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLetterArchives::route('/'), 'create' => Pages\CreateLetterArchive::route('/create'), 'edit' => Pages\EditLetterArchive::route('/{record}/edit')];
    }
}
