<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LetterNumberSequenceResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterNumberSequence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterNumberSequenceResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = LetterNumberSequence::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Sequence Nomor';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('jenis_surat_id')->relationship('jenisSurat', 'name')->label('Jenis Surat')->searchable()->preload(),
            Forms\Components\TextInput::make('year')->label('Tahun')->numeric()->required(),
            Forms\Components\TextInput::make('month')->label('Bulan')->numeric()->default(0),
            Forms\Components\TextInput::make('current_sequence')->label('Sequence')->numeric()->required(),
            Forms\Components\Select::make('reset_policy')->default('YEARLY')->options(['YEARLY' => 'Yearly', 'MONTHLY' => 'Monthly', 'NEVER' => 'Never']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('jenisSurat.code')->label('Jenis')->badge()->placeholder('Global'),
            Tables\Columns\TextColumn::make('year')->label('Tahun')->sortable(),
            Tables\Columns\TextColumn::make('month')->label('Bulan')->sortable(),
            Tables\Columns\TextColumn::make('current_sequence')->label('Sequence')->sortable(),
            Tables\Columns\TextColumn::make('reset_policy')->label('Reset')->badge(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLetterNumberSequences::route('/'), 'create' => Pages\CreateLetterNumberSequence::route('/create'), 'edit' => Pages\EditLetterNumberSequence::route('/{record}/edit')];
    }
}
