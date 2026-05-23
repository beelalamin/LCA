<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceLogResource\Pages;
use App\Models\MaintenanceLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenanceLogResource extends Resource
{
    protected static ?string $model = MaintenanceLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string { return __('Maintenance'); }
    public static function getPluralModelLabel(): string { return __('Maintenance Logs'); }
    public static function getNavigationLabel(): string { return __('Maintenance Logs'); }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System Administration');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('asset_id')
                    ->label(__('Asset'))
                    ->relationship('asset', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('type')->label(__('Type')),
                Forms\Components\TextInput::make('status')->label(__('Status')),
                Forms\Components\Textarea::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('scheduled_date')->label(__('Scheduled Date')),
                Forms\Components\DatePicker::make('completed_date')->label(__('Completed Date')),
                Forms\Components\TextInput::make('cost')->label(__('Cost'))->numeric()->prefix('QAR'),
                Forms\Components\TextInput::make('vendor')->label(__('Vendor')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset.name')->label(__('Asset'))->searchable(),
                Tables\Columns\TextColumn::make('type')->label(__('Type'))->badge(),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge(),
                Tables\Columns\TextColumn::make('technician.display_name')->label(__('Technician')),
                Tables\Columns\TextColumn::make('scheduled_date')->label(__('Scheduled Date'))->date(),
                Tables\Columns\TextColumn::make('completed_date')->label(__('Completed Date'))->date(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMaintenanceLogs::route('/'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['asset', 'technician']);
    }
}
