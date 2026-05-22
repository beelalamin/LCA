<?php

namespace App\Filament\Resources\Lookups\Concerns;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

trait LookupResourceSchema
{
    public static function getNavigationGroup(): ?string
    {
        return __('Lookups');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::formSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::tableColumns())
            ->defaultSort('sort_order')
            ->filters([
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

    protected static function formSchema(): array
    {
        return [
            Forms\Components\TextInput::make('code')
                ->label(__('Code'))
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText(__('Machine key, lowercase / underscores')),
            Forms\Components\Tabs::make('Translations')
                ->tabs([
                    Forms\Components\Tabs\Tab::make(__('English'))->schema([
                        Forms\Components\TextInput::make('name.en')->label(__('Name (EN)'))->required(),
                        Forms\Components\Textarea::make('description.en')->label(__('Description (EN)')),
                    ]),
                    Forms\Components\Tabs\Tab::make(__('Arabic'))->schema([
                        Forms\Components\TextInput::make('name.ar')->label(__('Name (AR)')),
                        Forms\Components\Textarea::make('description.ar')->label(__('Description (AR)')),
                    ]),
                ])->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')
                ->label(__('Sort Order'))
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_active')
                ->label(__('Active'))
                ->default(true),
        ];
    }

    protected static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('code')
                ->label(__('Code'))
                ->searchable(),
            Tables\Columns\TextColumn::make('name')
                ->label(__('Name'))
                ->searchable()
                ->formatStateUsing(fn ($state, $record) => $record->getTranslatedName()),
            Tables\Columns\TextColumn::make('sort_order')
                ->label(__('Sort Order'))
                ->sortable(),
            Tables\Columns\IconColumn::make('is_active')
                ->label(__('Active'))
                ->boolean(),
        ];
    }
}
