<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RekomendasiPengampuResource\Pages;
use App\Filament\Admin\Resources\RekomendasiPengampuResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\RekomendasiPengampu;
use App\Services\Sifak\DosenRecommendationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RekomendasiPengampuResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = RekomendasiPengampu::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'M5 Analytics';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('mata_kuliah_id')->relationship('mataKuliah', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload(),
                Forms\Components\Select::make('dosen_id')->relationship('dosen', 'name')->searchable()->preload()->required(),
                Forms\Components\TextInput::make('ranking')->numeric()->required()->minValue(1),
                Forms\Components\TextInput::make('score')->numeric()->required()->minValue(0)->maxValue(100),
                Forms\Components\Select::make('status')->required()->default('generated')->options([
                    'generated' => 'Generated',
                    'reviewed' => 'Reviewed',
                    'accepted' => 'Accepted',
                    'rejected' => 'Rejected',
                    'superseded' => 'Superseded',
                ]),
                Forms\Components\Textarea::make('summary_reason')->label('Alasan Ringkas')->columnSpanFull(),
                Forms\Components\Textarea::make('justification')->label('Justifikasi')->columnSpanFull(),
                Forms\Components\KeyValue::make('score_breakdown')->label('Breakdown')->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ranking')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('mataKuliah.name')->label('MK')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('score')->label('Skor')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('summary_reason')->label('Alasan')->limit(60),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check')
                    ->visible(fn (RekomendasiPengampu $record) => in_array($record->status, ['generated', 'reviewed'], true) && (auth()->user()?->can('accept_dosen_recommendation') || auth()->user()?->can('update_rekomendasi::pengampu')))
                    ->form([
                        Forms\Components\Textarea::make('justification')->label('Justifikasi')->helperText('Wajib jika skor di bawah threshold.'),
                    ])
                    ->action(fn (RekomendasiPengampu $record, array $data) => app(DosenRecommendationService::class)->accept($record, $data['justification'] ?? null)),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (RekomendasiPengampu $record) => in_array($record->status, ['generated', 'reviewed'], true) && (auth()->user()?->can('accept_dosen_recommendation') || auth()->user()?->can('update_rekomendasi::pengampu')))
                    ->form([
                        Forms\Components\Textarea::make('justification')->label('Alasan'),
                    ])
                    ->action(fn (RekomendasiPengampu $record, array $data) => app(DosenRecommendationService::class)->reject($record, $data['justification'] ?? null)),
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
            'index' => Pages\ListRekomendasiPengampus::route('/'),
            'create' => Pages\CreateRekomendasiPengampu::route('/create'),
            'edit' => Pages\EditRekomendasiPengampu::route('/{record}/edit'),
        ];
    }
}
