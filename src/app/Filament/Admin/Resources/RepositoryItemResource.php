<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RepositoryItemResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\RepositoryItem;
use App\Services\Sifak\TaRepositoryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RepositoryItemResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = RepositoryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'M7 Dokumen TA';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tugas_akhir_id')->relationship('tugasAkhir', 'judul')->searchable()->preload()->required(),
            Forms\Components\Select::make('final_file_id')->relationship('finalFile', 'original_name')->searchable()->preload(),
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('abstract_id')->label('Abstrak ID')->columnSpanFull(),
            Forms\Components\Textarea::make('abstract_en')->label('Abstract EN')->columnSpanFull(),
            Forms\Components\TagsInput::make('keywords')->columnSpanFull(),
            Forms\Components\TextInput::make('author_name')->required(),
            Forms\Components\TextInput::make('nim')->required(),
            Forms\Components\Select::make('program_studi_id')->relationship('programStudi', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('year')->numeric(),
            Forms\Components\Select::make('access_level')->required()->default('internal')->options([
                'private' => 'Private',
                'internal' => 'Internal',
                'public_metadata' => 'Public Metadata',
                'public_fulltext' => 'Public Fulltext',
            ]),
            Forms\Components\Select::make('status')->required()->default('draft')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nim')->searchable(),
            Tables\Columns\TextColumn::make('author_name')->label('Penulis')->searchable(),
            Tables\Columns\TextColumn::make('title')->limit(70)->searchable(),
            Tables\Columns\TextColumn::make('access_level')->badge(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('published_at')->dateTime(),
        ])->actions([
            Tables\Actions\Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn (RepositoryItem $record) => $record->status !== 'published' && auth()->user()?->can('publish_ta_repository'))
                ->action(fn (RepositoryItem $record) => app(TaRepositoryService::class)->publish($record)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepositoryItems::route('/'),
            'create' => Pages\CreateRepositoryItem::route('/create'),
            'edit' => Pages\EditRepositoryItem::route('/{record}/edit'),
        ];
    }
}
