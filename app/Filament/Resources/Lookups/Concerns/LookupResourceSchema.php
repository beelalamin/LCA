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
        ];
    }

    protected static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('Name'))
                ->searchable()
                ->formatStateUsing(fn ($state, $record) => $record->getTranslatedName()),
        ];
    }
}
