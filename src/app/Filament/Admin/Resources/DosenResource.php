<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DosenResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Dosen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DosenResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Dosen::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Profil Dosen')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('nidn')->required()->maxLength(32)->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(150),
                    Forms\Components\TextInput::make('email')->email()->maxLength(150),
                    Forms\Components\Select::make('program_studi_id')->relationship('programStudi', 'name')->searchable()->preload(),
                    Forms\Components\TextInput::make('academic_position')->label('Jabatan akademik')->maxLength(100),
                    Forms\Components\Select::make('last_education')->label('Pendidikan terakhir')->options(['S2' => 'S2', 'S3' => 'S3']),
                    Forms\Components\Select::make('rumpun_ilmu_id')->relationship('rumpunIlmu', 'name')->searchable()->preload(),
                    Forms\Components\Select::make('kbk_id')->relationship('kbk', 'name')->searchable()->preload(),
                    Forms\Components\TagsInput::make('skills')->label('Keahlian spesifik')->columnSpanFull(),
                    Forms\Components\KeyValue::make('education')->label('Pendidikan')->columnSpanFull(),
                    Forms\Components\TagsInput::make('certifications')->label('Sertifikasi')->columnSpanFull(),
                    Forms\Components\TagsInput::make('publications')->label('Publikasi')->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Beban')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('teaching_load_sks')->label('SKS mengajar')->numeric()->required()->minValue(0),
                    Forms\Components\TextInput::make('guidance_load')->label('Bimbingan')->numeric()->required()->minValue(0),
                    Forms\Components\TextInput::make('examiner_load')->label('Penguji')->numeric()->required()->minValue(0),
                    Forms\Components\Select::make('status')
                        ->required()
                        ->default('active')
                        ->options(['active' => 'Aktif', 'inactive' => 'Tidak aktif', 'leave' => 'Cuti']),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nidn')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->toggleable()->searchable(),
                Tables\Columns\TextColumn::make('programStudi.name')->label('Prodi'),
                Tables\Columns\TextColumn::make('last_education')->label('Pendidikan')->toggleable(),
                Tables\Columns\TextColumn::make('rumpunIlmu.name')->label('Rumpun'),
                Tables\Columns\TextColumn::make('kbk.name')->label('KBK'),
                Tables\Columns\TextColumn::make('teaching_load_sks')->label('SKS'),
                Tables\Columns\TextColumn::make('guidance_load')->label('Bimbingan'),
                Tables\Columns\TextColumn::make('examiner_load')->label('Penguji'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDosens::route('/'),
            'create' => Pages\CreateDosen::route('/create'),
            'edit' => Pages\EditDosen::route('/{record}/edit'),
        ];
    }
}
