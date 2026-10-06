<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MahasiswaResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Mahasiswa;
use App\Services\Sifak\AcademicAlertEngine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MahasiswaResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Mahasiswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('nim')->required()->maxLength(32)->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(150),
                    Forms\Components\TextInput::make('email')->email()->maxLength(150),
                    Forms\Components\TextInput::make('phone')->label('No. HP')->tel()->maxLength(30),
                    Forms\Components\Select::make('program_studi_id')
                        ->relationship('programStudi', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('angkatan')->numeric()->required()->minValue(2000)->maxValue(2100),
                    Forms\Components\TextInput::make('semester')->numeric()->required()->minValue(1)->maxValue(14),
                    Forms\Components\TextInput::make('ipk')->numeric()->required()->minValue(0)->maxValue(4)->step('0.01'),
                    Forms\Components\TextInput::make('sks_lulus')->label('SKS lulus')->numeric()->required()->minValue(0)->maxValue(200),
                    Forms\Components\Select::make('status')
                        ->required()
                        ->default('active')
                        ->options(['active' => 'Aktif', 'leave' => 'Cuti', 'graduated' => 'Lulus', 'inactive' => 'Tidak aktif']),
                ]),
            Forms\Components\Section::make('Profil M6')
                ->schema([
                    Forms\Components\TagsInput::make('interests')->label('Minat'),
                    Forms\Components\KeyValue::make('profile_payload')->label('Portofolio / MBKM / Organisasi'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nim')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->toggleable()->searchable(),
                Tables\Columns\TextColumn::make('programStudi.name')->label('Prodi')->sortable(),
                Tables\Columns\TextColumn::make('angkatan')->sortable(),
                Tables\Columns\TextColumn::make('semester')->sortable(),
                Tables\Columns\TextColumn::make('ipk')->sortable(),
                Tables\Columns\TextColumn::make('sks_lulus')->label('SKS')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('program_studi_id')->relationship('programStudi', 'name')->label('Prodi'),
            ])
            ->actions([
                Tables\Actions\Action::make('evaluateAlerts')
                    ->label('Evaluasi alert')
                    ->icon('heroicon-o-bell-alert')
                    ->action(function (Mahasiswa $record) {
                        $alerts = app(AcademicAlertEngine::class)->evaluate($record);

                        Notification::make()
                            ->title(count($alerts) . ' alert aktif dievaluasi')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMahasiswas::route('/'),
            'create' => Pages\CreateMahasiswa::route('/create'),
            'edit' => Pages\EditMahasiswa::route('/{record}/edit'),
        ];
    }
}
