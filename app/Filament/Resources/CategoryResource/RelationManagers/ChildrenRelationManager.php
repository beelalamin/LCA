<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('SubCategories');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Translations')
                ->tabs([
                    Forms\Components\Tabs\Tab::make(__('English'))
                        ->schema([
                            Forms\Components\TextInput::make('name.en')
                                ->label(__('Name (EN)'))
                                ->required(),
                        ]),
                    Forms\Components\Tabs\Tab::make(__('Arabic'))
                        ->schema([
                            Forms\Components\TextInput::make('name.ar')
                                ->label(__('Name (AR)')),
                        ]),
                ])->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('assets'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslation('name', app()->getLocale())),
                Tables\Columns\TextColumn::make('assets_count')
                    ->label(__('Assets Count')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('New SubCategory')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn ($record) => ($record->assets_count ?? 0) > 0)
                    ->tooltip(fn ($record) => ($record->assets_count ?? 0) > 0
                        ? __('Cannot delete: subcategory has assets')
                        : null),
            ])
            ->bulkActions([]);
    }
}
