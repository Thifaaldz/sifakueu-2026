<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaCommentResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\TaComment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaCommentResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = TaComment::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'M7 Dokumen TA';
    protected static ?int $navigationSort = 6;

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
            Forms\Components\Textarea::make('comment')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('page_reference'),
            Forms\Components\TextInput::make('section_reference'),
            Forms\Components\Select::make('status')->default('open')->options(['open' => 'Open', 'resolved' => 'Resolved', 'closed' => 'Closed']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('version.document.tugasAkhir.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('version.document.section.code')->label('Bagian')->badge(),
            Tables\Columns\TextColumn::make('reviewer.name')->label('Reviewer')->searchable(),
            Tables\Columns\TextColumn::make('comment')->limit(80),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaComments::route('/'),
            'create' => Pages\CreateTaComment::route('/create'),
            'edit' => Pages\EditTaComment::route('/{record}/edit'),
        ];
    }

    private static function scopeVersionOptions($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('mahasiswa')) {
            return $query->whereHas('document.tugasAkhir.mahasiswa', fn ($builder) => $builder->where('user_id', $user->id));
        }

        if ($user?->hasAnyRole(['dosen', 'dosen_pembimbing', 'dosen_pa', 'dosen_penguji'])) {
            $dosenId = $user->dosen?->id;

            return $dosenId
                ? $query->whereHas('document.tugasAkhir', fn ($builder) => $builder->where('pembimbing_1_id', $dosenId)->orWhere('pembimbing_2_id', $dosenId))
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
