<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Filament\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use App\Enums\AssetStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Concerns\Translatable;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetResource extends Resource
{
    use Translatable;

    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('asset_tag')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Auto-generated on creation'),
                Forms\Components\TextInput::make('serial_number')
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('manufacturer'),
                Forms\Components\TextInput::make('model'),
                Forms\Components\Select::make('status')
                    ->options(\App\Enums\AssetStatus::class)
                    ->default(\App\Enums\AssetStatus::PURCHASED->value),
                Forms\Components\DatePicker::make('purchase_date'),
                Forms\Components\TextInput::make('purchase_cost')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\DatePicker::make('warranty_expiry'),
                Forms\Components\TextInput::make('location'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_tag')->searchable(),
                Tables\Columns\TextColumn::make('serial_number')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->sortable(),
                Tables\Columns\TextColumn::make('manufacturer')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PURCHASED' => 'gray',
                        'AVAILABLE' => 'info',
                        'ASSIGNED' => 'success',
                        'IN_REPAIR' => 'warning',
                        'RETIRED', 'DISPOSED' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('purchase_date')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(\App\Enums\AssetStatus::class),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageAssets::route('/'),
        ];
    }
}
