<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BebanDosenResource\Pages;
use App\Filament\Admin\Resources\BebanDosenResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\BebanDosen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BebanDosenResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = BebanDosen::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'M5 Analytics';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('dosen_id')->relationship('dosen', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload(),
                Forms\Components\TextInput::make('teaching_sks')->label('SKS Mengajar')->numeric()->required()->minValue(0),
                Forms\Components\TextInput::make('guidance_count')->label('Bimbingan')->numeric()->required()->minValue(0),
                Forms\Components\TextInput::make('examiner_count')->label('Penguji')->numeric()->required()->minValue(0),
                Forms\Components\TextInput::make('research_load')->label('Penelitian')->numeric()->required()->minValue(0),
                Forms\Components\TextInput::make('workload_score')->label('Skor Beban')->numeric()->required()->minValue(0),
                Forms\Components\Select::make('workload_status')->label('Status')->required()->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'overload' => 'Overload']),
                Forms\Components\DateTimePicker::make('calculated_at')->label('Dihitung pada'),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('semester.code')->label('Semester')->toggleable(),
                Tables\Columns\TextColumn::make('teaching_sks')->label('SKS')->sortable(),
                Tables\Columns\TextColumn::make('guidance_count')->label('Bimbingan')->sortable(),
                Tables\Columns\TextColumn::make('examiner_count')->label('Penguji')->sortable(),
                Tables\Columns\TextColumn::make('workload_score')->label('Skor')->sortable(),
                Tables\Columns\TextColumn::make('workload_status')->label('Status')->badge(),
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
            'index' => Pages\ListBebanDosens::route('/'),
            'create' => Pages\CreateBebanDosen::route('/create'),
            'edit' => Pages\EditBebanDosen::route('/{record}/edit'),
        ];
    }
}
