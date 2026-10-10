<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApprovalFlowResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\ApprovalFlow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApprovalFlowResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = ApprovalFlow::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Approval Flow';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Kode')->required()->maxLength(60),
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
            Forms\Components\Toggle::make('active')->label('Aktif')->default(true),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable(),
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
            Tables\Columns\IconColumn::make('active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('steps_count')->counts('steps')->label('Step'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListApprovalFlows::route('/'), 'create' => Pages\CreateApprovalFlow::route('/create'), 'edit' => Pages\EditApprovalFlow::route('/{record}/edit')];
    }
}
