<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MahasiswaMbkmResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MahasiswaMbkm;
use App\Filament\Support\MahasiswaOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MahasiswaMbkmResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MahasiswaMbkm::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';
    protected static ?string $navigationLabel = 'MBKM/Magang';

    public static function form(Form $form): Form
    {
        return $form->schema([
            MahasiswaOwnership::field(),
            Forms\Components\TextInput::make('program_type')->label('Program')->required()->maxLength(80),
            Forms\Components\TextInput::make('institution')->label('Institusi')->maxLength(255),
            Forms\Components\TextInput::make('role')->label('Peran')->maxLength(255),
            Forms\Components\TextInput::make('field')->label('Bidang')->maxLength(255),
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
            Tables\Columns\TextColumn::make('program_type')->label('Program')->badge()->searchable(),
            Tables\Columns\TextColumn::make('institution')->label('Institusi')->searchable(),
            Tables\Columns\TextColumn::make('role')->label('Peran'),
            Tables\Columns\TextColumn::make('field')->label('Bidang'),
            Tables\Columns\TextColumn::make('start_date')->label('Mulai')->date(),
            Tables\Columns\TextColumn::make('end_date')->label('Selesai')->date(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMahasiswaMbkms::route('/'), 'create' => Pages\CreateMahasiswaMbkm::route('/create'), 'edit' => Pages\EditMahasiswaMbkm::route('/{record}/edit')];
    }
}
