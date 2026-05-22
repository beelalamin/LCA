<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Filament\Resources\Lookups\StatusResource\Pages;
use App\Models\Lookups\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StatusResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = Status::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string { return __('Status'); }
    public static function getPluralModelLabel(): string { return __('Statuses'); }
    public static function getNavigationLabel(): string { return __('Statuses'); }

    public static function form(Form $form): Form
    {
        $schema = static::formSchema();

        array_splice($schema, 1, 0, [
            Forms\Components\Select::make('scope')
                ->label(__('Scope'))
                ->options([
                    'asset' => __('Asset'),
                    'user' => __('User'),
                ])
                ->default('asset')
                ->required(),
            Forms\Components\TextInput::make('color')
                ->label(__('Color'))
                ->placeholder('success | warning | danger | info | gray'),
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
                Tables\Columns\TextColumn::make('scope')->label(__('Scope'))->badge(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('Sort'))->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('scope')->options([
                    'asset' => __('Asset'),
                    'user' => __('User'),
                ]),
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
            'index' => Pages\ManageStatuses::route('/'),
        ];
    }
}
