<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaReviewResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\TaReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaReviewResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = TaReview::class;
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationGroup = 'M7 Dokumen TA';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('ta_document_version_id')
                ->relationship('version', 'version_number', modifyQueryUsing: fn ($query) => self::scopeVersionOptions($query))
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('reviewer_id')
                ->relationship('reviewer', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->dosen?->id ? $query->whereKey(auth()->user()->dosen->id) : $query)
                ->default(fn () => auth()->user()?->dosen?->id)
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('review_status')->default('pending')->options([
                'pending' => 'Pending',
                'reviewed' => 'Reviewed',
                'revision_required' => 'Revision Required',
                'approved' => 'Approved',
            ]),
            Forms\Components\DateTimePicker::make('reviewed_at'),
            Forms\Components\Textarea::make('summary')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('version.document.tugasAkhir.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('version.document.section.code')->label('Bagian')->badge(),
            Tables\Columns\TextColumn::make('reviewer.name')->searchable(),
            Tables\Columns\TextColumn::make('review_status')->badge(),
            Tables\Columns\TextColumn::make('reviewed_at')->dateTime(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaReviews::route('/'),
            'create' => Pages\CreateTaReview::route('/create'),
            'edit' => Pages\EditTaReview::route('/{record}/edit'),
        ];
    }

    private static function scopeVersionOptions($query)
    {
        $user = auth()->user();

        if ($user?->hasAnyRole(['dosen', 'dosen_pembimbing', 'dosen_pa', 'dosen_penguji'])) {
            $dosenId = $user->dosen?->id;

            return $dosenId
                ? $query->whereHas('document.tugasAkhir', fn ($builder) => $builder->where('pembimbing_1_id', $dosenId)->orWhere('pembimbing_2_id', $dosenId))
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
