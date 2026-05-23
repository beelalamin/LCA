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

    protected static function scopeOptions(): array
    {
        return [
            Status::SCOPE_ASSET => __('Asset'),
            Status::SCOPE_USER => __('User'),
            Status::SCOPE_WARRANTY => __('Warranty'),
            Status::SCOPE_ASSIGNMENT => __('Assignment'),
            Status::SCOPE_MAINTENANCE => __('Maintenance'),
        ];
    }

    public static function form(Form $form): Form
    {
        $schema = static::formSchema();

        array_unshift($schema, Forms\Components\Select::make('scope')
            ->label(__('Scope'))
            ->options(static::scopeOptions())
            ->default(Status::SCOPE_ASSET)
            ->required());

        $schema[] = Forms\Components\TextInput::make('color')
            ->label(__('Color'))
            ->placeholder('success | warning | danger | info | gray');

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('scope')
                    ->label(__('Scope'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => static::scopeOptions()[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslatedName()),
                Tables\Columns\TextColumn::make('color')
                    ->label(__('Color'))
                    ->badge()
                    ->color(fn ($record) => $record->getColour()),
            ])
            ->defaultSort('scope')
            ->filters([
                Tables\Filters\SelectFilter::make('scope')->options(static::scopeOptions()),
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
