<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SuratApprovalResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SuratApproval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SuratApprovalResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SuratApproval::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Approval Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('surat_id')->relationship('surat', 'subject')->searchable()->preload()->required(),
            Forms\Components\Select::make('approver_id')->relationship('approver', 'name')->label('Approver')->searchable()->preload(),
            Forms\Components\TextInput::make('role_name')->label('Role')->required()->maxLength(80),
            Forms\Components\TextInput::make('sequence')->label('Urutan')->numeric()->required(),
            Forms\Components\Select::make('status')->default('PENDING')->options(['PENDING' => 'Pending', 'APPROVED' => 'Approved', 'REJECTED' => 'Rejected', 'REVISION_REQUIRED' => 'Revision', 'SKIPPED' => 'Skipped']),
            Forms\Components\Textarea::make('notes')->label('Catatan')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('surat.request_number')->label('Pengajuan')->searchable(),
            Tables\Columns\TextColumn::make('surat.subject')->label('Perihal')->limit(35)->searchable(),
            Tables\Columns\TextColumn::make('role_name')->label('Role')->badge(),
            Tables\Columns\TextColumn::make('sequence')->label('Urutan')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('approver.name')->label('Approver')->placeholder('-'),
            Tables\Columns\TextColumn::make('acted_at')->dateTime()->placeholder('-'),
        ])->defaultSort('sequence')->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSuratApprovals::route('/'), 'create' => Pages\CreateSuratApproval::route('/create'), 'edit' => Pages\EditSuratApproval::route('/{record}/edit')];
    }
}
