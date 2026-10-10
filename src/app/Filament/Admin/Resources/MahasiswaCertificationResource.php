<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MahasiswaCertificationResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MahasiswaCertification;
use App\Filament\Support\MahasiswaOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MahasiswaCertificationResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MahasiswaCertification::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';
    protected static ?string $navigationLabel = 'Sertifikasi Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            MahasiswaOwnership::field(),
            Forms\Components\TextInput::make('name')->label('Nama Sertifikasi')->required()->maxLength(255),
            Forms\Components\TextInput::make('issuer')->label('Penerbit')->maxLength(255),
            Forms\Components\TextInput::make('field')->label('Bidang')->maxLength(255),
            Forms\Components\DatePicker::make('issued_at')->label('Terbit'),
            Forms\Components\DatePicker::make('expired_at')->label('Kedaluwarsa'),
            Forms\Components\Toggle::make('verified')->label('Terverifikasi')->default(false)->visible(fn () => ! MahasiswaOwnership::isSelfService()),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('name')->label('Sertifikasi')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('issuer')->label('Penerbit')->searchable(),
            Tables\Columns\TextColumn::make('field')->label('Bidang')->badge(),
            Tables\Columns\TextColumn::make('issued_at')->label('Terbit')->date()->sortable(),
            Tables\Columns\IconColumn::make('verified')->label('Valid')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMahasiswaCertifications::route('/'), 'create' => Pages\CreateMahasiswaCertification::route('/create'), 'edit' => Pages\EditMahasiswaCertification::route('/{record}/edit')];
    }
}
