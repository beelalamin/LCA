<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('Category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Categories');
    }

    public static function getNavigationLabel(): string
    {
        return __('Categories');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Lookups');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'asset_manager']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('parent_id')
                    ->label(__('Parent Category'))
                    ->placeholder(__('— (Top level)'))
                    ->options(function (?Category $record) {
                        return Category::query()
                            ->whereNull('parent_id')
                            ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('name', app()->getLocale())]);
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText(__('Leave empty to create a top-level category.'))
                    ->columnSpanFull(),
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

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Category'))
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslation('name', app()->getLocale())),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('Parent'))
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state, $record) => $record->parent?->getTranslation('name', app()->getLocale())),
                Tables\Columns\TextColumn::make('children_count')
                    ->label(__('SubCategories'))
                    ->counts('children'),
                Tables\Columns\TextColumn::make('assets_count')
                    ->label(__('Assets Count'))
                    ->counts('assets'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label(__('Parent'))
                    ->options(fn () => Category::query()
                        ->whereNull('parent_id')
                        ->get()
                        ->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('name', app()->getLocale())])),
                Tables\Filters\TernaryFilter::make('top_level_only')
                    ->label(__('Top level only'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Top level only'))
                    ->falseLabel(__('Subcategories only'))
                    ->queries(
                        true: fn ($query) => $query->whereNull('parent_id'),
                        false: fn ($query) => $query->whereNotNull('parent_id'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->disabled(fn ($record) => ($record->assets_count ?? 0) > 0 || ($record->children_count ?? 0) > 0)
                        ->tooltip(fn ($record) => (($record->assets_count ?? 0) > 0 || ($record->children_count ?? 0) > 0)
                            ? __('Cannot delete: category has assets or subcategories')
                            : null),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CategoryResource\RelationManagers\ChildrenRelationManager::class,
            CategoryResource\RelationManagers\AssetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'view' => Pages\ViewCategory::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('parent')
            ->withCount(['assets', 'children']);
    }
}
