<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function getModelLabel(): string
    {
        return __('Category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Categories');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Translations')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\TextInput::make('name.en')
                                    ->label(__('Name (EN)'))
                                    ->required(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Arabic')
                            ->schema([
                                Forms\Components\TextInput::make('name.ar')
                                    ->label(__('Name (AR)')),
                            ]),
                    ])->columnSpanFull(),
                Forms\Components\Select::make('parent_id')
                    ->label(__('Parent Category'))
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Category'))
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslation('name', app()->getLocale())),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('Parent Category'))
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $record->parent?->getTranslation('name', app()->getLocale())),
                Tables\Columns\TextColumn::make('assets_count')->label(__('Assets Count'))->counts('assets'),
            ])
            ->filters([
                //
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
            'index' => Pages\ManageCategories::route('/'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['parent'])->withCount(['assets']);
    }
}
