<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApprovalFlowStepResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\ApprovalFlowStep;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApprovalFlowStepResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = ApprovalFlowStep::class;
    protected static ?string $navigationIcon = 'heroicon-o-numbered-list';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Approval Step';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('approval_flow_id')->relationship('approvalFlow', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('step_order')->label('Urutan')->numeric()->required(),
            Forms\Components\TextInput::make('role_code')->label('Role')->required()->maxLength(80),
            Forms\Components\Select::make('approval_type')->label('Tipe')->default('APPROVAL')->options(['APPROVAL' => 'Approval', 'ACKNOWLEDGEMENT' => 'Acknowledgement', 'VERIFICATION' => 'Verification']),
            Forms\Components\Toggle::make('required')->label('Wajib')->default(true),
            Forms\Components\Toggle::make('can_reject')->label('Bisa Tolak')->default(true),
            Forms\Components\Toggle::make('can_request_revision')->label('Bisa Revisi')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('approvalFlow.name')->label('Flow')->searchable(),
            Tables\Columns\TextColumn::make('step_order')->label('Urutan')->sortable(),
            Tables\Columns\TextColumn::make('role_code')->label('Role')->badge(),
            Tables\Columns\TextColumn::make('approval_type')->label('Tipe')->badge(),
            Tables\Columns\IconColumn::make('required')->boolean(),
        ])->defaultSort('step_order')->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListApprovalFlowSteps::route('/'), 'create' => Pages\CreateApprovalFlowStep::route('/create'), 'edit' => Pages\EditApprovalFlowStep::route('/{record}/edit')];
    }
}
