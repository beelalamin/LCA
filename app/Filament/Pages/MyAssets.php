<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyAssets extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static string $view = 'filament.pages.list-records';
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('My Assets');
    }

    public function getTitle(): string
    {
        return __('My Assets');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('My Workspace');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('user') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(static::getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('asset_tag')->label(__('Asset Tag'))->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslation('name', app()->getLocale())),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->formatStateUsing(fn ($state, $record) => $record->category?->getTranslation('name', app()->getLocale())),
                Tables\Columns\TextColumn::make('status.code')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->status?->getTranslatedName())
                    ->color(fn ($record) => $record->status?->getColour() ?? 'gray'),
                Tables\Columns\TextColumn::make('assigned_date')->label(__('Assigned'))->date(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => \App\Filament\Resources\AssetResource::getUrl('view', ['record' => $record])),
            ]);
    }

    protected static function getTableQuery(): Builder
    {
        $userId = auth()->id();

        return Asset::query()
            ->with(['category', 'status'])
            ->when($userId, fn ($q) => $q->where('assigned_to_user_id', $userId))
            ->when(! $userId, fn ($q) => $q->whereRaw('1 = 0'));
    }
}
