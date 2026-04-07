<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceLogResource\Pages;
use App\Models\MaintenanceLog;
use App\Enums\MaintenanceStatus;
use App\Enums\MaintenanceType;
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
                    ->relationship('asset', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('type')
                    ->options(MaintenanceType::class)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(MaintenanceStatus::class)
                    ->required()
                    ->default('OPEN'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('scheduled_date'),
                Forms\Components\DatePicker::make('completed_date'),
                Forms\Components\TextInput::make('cost')->numeric()->prefix('$'),
                Forms\Components\TextInput::make('vendor'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset.name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('technician.full_name'),
                Tables\Columns\TextColumn::make('scheduled_date')->date(),
                Tables\Columns\TextColumn::make('completed_date')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(fn () => collect(MaintenanceStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->toArray()),
                Tables\Filters\SelectFilter::make('type')->options(fn () => collect(MaintenanceType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->toArray()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical')
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
