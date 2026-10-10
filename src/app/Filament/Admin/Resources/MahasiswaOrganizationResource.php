<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MahasiswaOrganizationResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MahasiswaOrganization;
use App\Filament\Support\MahasiswaOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MahasiswaOrganizationResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MahasiswaOrganization::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';
    protected static ?string $navigationLabel = 'Organisasi Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            MahasiswaOwnership::field(),
            Forms\Components\TextInput::make('organization_name')->label('Organisasi')->required()->maxLength(255),
            Forms\Components\TextInput::make('role')->label('Peran')->maxLength(255),
            Forms\Components\DatePicker::make('start_date')->label('Mulai'),
            Forms\Components\DatePicker::make('end_date')->label('Selesai'),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('organization_name')->label('Organisasi')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('role')->label('Peran')->badge(),
            Tables\Columns\TextColumn::make('start_date')->label('Mulai')->date(),
            Tables\Columns\TextColumn::make('end_date')->label('Selesai')->date(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMahasiswaOrganizations::route('/'), 'create' => Pages\CreateMahasiswaOrganization::route('/create'), 'edit' => Pages\EditMahasiswaOrganization::route('/{record}/edit')];
    }
}
