<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\AssetModelResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\AssetModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssetModelResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = AssetModel::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?int $navigationSort = 25;

    public static function getModelLabel(): string { return __('Model'); }
    public static function getPluralModelLabel(): string { return __('Models'); }
    public static function getNavigationLabel(): string { return __('Models'); }

    public static function form(Form $form): Form
    {
        $schema = static::formSchema();

        array_splice($schema, 1, 0, [
            Forms\Components\Select::make('manufacturer_id')
                ->label(__('Manufacturer'))
                ->relationship('manufacturer', 'code')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslatedName())
                ->searchable()
                ->preload(),
        ]);

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label(__('Code'))->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslatedName()),
                Tables\Columns\TextColumn::make('manufacturer.code')
                    ->label(__('Manufacturer'))
                    ->formatStateUsing(fn ($state, $record) => $record->manufacturer?->getTranslatedName())
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('Sort'))->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('manufacturer_id')
                    ->relationship('manufacturer', 'code'),
                Tables\Filters\TernaryFilter::make('is_active')->label(__('Active')),
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
            'index' => Pages\ManageAssetModels::route('/'),
        ];
    }
}
