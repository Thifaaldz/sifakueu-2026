<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DosenProfilResource\Pages;
use App\Filament\Admin\Resources\DosenProfilResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\DosenProfil;
use App\Filament\Support\DosenOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DosenProfilResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = DosenProfil::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'M5 Profiling Dosen';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DosenOwnership::field(),
                Forms\Components\Select::make('profile_status')->label('Status Profil')->required()->default('draft')->disabled(fn () => DosenOwnership::isSelfService())->dehydrated()->options([
                    'draft' => 'Draft',
                    'data_completed' => 'Data Completed',
                    'submitted' => 'Submitted',
                    'verified' => 'Verified',
                    'revision_required' => 'Revision Required',
                ]),
                Forms\Components\Textarea::make('profile_summary')->label('Ringkasan Profil')->columnSpanFull(),
                Forms\Components\Textarea::make('expertise_focus')->label('Fokus Keahlian')->columnSpanFull(),
                Forms\Components\Textarea::make('industry_experience_summary')->label('Ringkasan Pengalaman Industri')->columnSpanFull(),
                Forms\Components\Select::make('verified_by')->relationship('verifier', 'email')->visible(fn () => ! DosenOwnership::isSelfService())->searchable()->preload(),
                Forms\Components\DateTimePicker::make('verified_at')->label('Diverifikasi pada')->visible(fn () => ! DosenOwnership::isSelfService()),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('dosen.nidn')->label('NIDN')->searchable(),
                Tables\Columns\TextColumn::make('profile_status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('verifier.email')->label('Verifier')->toggleable(),
                Tables\Columns\TextColumn::make('verified_at')->dateTime()->toggleable(),
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
            'index' => Pages\ListDosenProfils::route('/'),
            'create' => Pages\CreateDosenProfil::route('/create'),
            'edit' => Pages\EditDosenProfil::route('/{record}/edit'),
        ];
    }
}
