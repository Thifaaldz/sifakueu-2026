<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RiwayatMengajarResource\Pages;
use App\Filament\Admin\Resources\RiwayatMengajarResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\RiwayatMengajar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RiwayatMengajarResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = RiwayatMengajar::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'M5 Profiling Dosen';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('dosen_id')->relationship('dosen', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('mata_kuliah_id')->relationship('mataKuliah', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('tahun_akademik_id')->relationship('tahunAkademik', 'code')->searchable()->preload(),
                Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload(),
                Forms\Components\TextInput::make('sks')->numeric()->required()->minValue(0)->maxValue(12),
                Forms\Components\TextInput::make('class_name')->label('Kelas')->maxLength(40),
                Forms\Components\TextInput::make('average_evaluation')->label('Evaluasi Rata-rata')->numeric()->minValue(0)->maxValue(5)->step('0.01'),
                Forms\Components\TextInput::make('student_count')->label('Jumlah Mahasiswa')->numeric()->minValue(0),
                Forms\Components\Select::make('status')->default('completed')->options(['planned' => 'Planned', 'active' => 'Active', 'completed' => 'Completed']),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('mataKuliah.name')->label('MK')->searchable(),
                Tables\Columns\TextColumn::make('semester.code')->label('Semester')->toggleable(),
                Tables\Columns\TextColumn::make('sks')->sortable(),
                Tables\Columns\TextColumn::make('average_evaluation')->label('Evaluasi')->sortable(),
                Tables\Columns\TextColumn::make('student_count')->label('Mhs')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
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
            'index' => Pages\ListRiwayatMengajars::route('/'),
            'create' => Pages\CreateRiwayatMengajar::route('/create'),
            'edit' => Pages\EditRiwayatMengajar::route('/{record}/edit'),
        ];
    }
}
