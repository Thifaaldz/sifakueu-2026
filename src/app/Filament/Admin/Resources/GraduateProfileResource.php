<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GraduateProfileResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\GraduateProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GraduateProfileResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = GraduateProfile::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('program_studi_id')->relationship('programStudi', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('code')->required()->maxLength(60),
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
            Forms\Components\Toggle::make('active')->default(true),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('programStudi.name')->label('Prodi')->toggleable(),
            Tables\Columns\TextColumn::make('code')->searchable()->badge(),
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
            Tables\Columns\IconColumn::make('active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListGraduateProfiles::route('/'), 'create' => Pages\CreateGraduateProfile::route('/create'), 'edit' => Pages\EditGraduateProfile::route('/{record}/edit')];
    }
}
