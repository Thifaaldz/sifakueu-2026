<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DosenSertifikasiResource\Pages;
use App\Filament\Admin\Resources\DosenSertifikasiResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\DosenSertifikasi;
use App\Filament\Support\DosenOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DosenSertifikasiResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = DosenSertifikasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'M5 Profiling Dosen';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DosenOwnership::field(),
                Forms\Components\TextInput::make('name')->label('Sertifikasi')->required()->maxLength(150),
                Forms\Components\TextInput::make('issuer')->label('Penerbit')->maxLength(150),
                Forms\Components\TextInput::make('field')->label('Bidang')->maxLength(150),
                Forms\Components\TextInput::make('certificate_number')->label('Nomor Sertifikat')->maxLength(120),
                Forms\Components\DatePicker::make('issued_on')->label('Tanggal Terbit'),
                Forms\Components\DatePicker::make('expires_on')->label('Tanggal Berakhir'),
                Forms\Components\Select::make('validation_status')->label('Status Validasi')->default('pending')->visible(fn () => ! DosenOwnership::isSelfService())->options(['pending' => 'Pending', 'validated' => 'Validated', 'rejected' => 'Rejected']),
                Forms\Components\TextInput::make('file_path')->label('File')->maxLength(255)->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Sertifikasi')->searchable(),
                Tables\Columns\TextColumn::make('issuer')->label('Penerbit')->toggleable(),
                Tables\Columns\TextColumn::make('field')->label('Bidang')->searchable(),
                Tables\Columns\TextColumn::make('validation_status')->label('Validasi')->badge(),
                Tables\Columns\TextColumn::make('expires_on')->date()->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDosenSertifikasis::route('/'),
            'create' => Pages\CreateDosenSertifikasi::route('/create'),
            'edit' => Pages\EditDosenSertifikasi::route('/{record}/edit'),
        ];
    }
}
