<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make(__('Category'))->schema([
                TextEntry::make('name')
                    ->label(__('Name'))
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslation('name', app()->getLocale())),
                TextEntry::make('children_count')
                    ->label(__('SubCategories'))
                    ->state(fn ($record) => $record->children()->count()),
                TextEntry::make('assets_count')
                    ->label(__('Assets Count'))
                    ->state(fn ($record) => $record->assets()->count()),
            ])->columns(3),
        ]);
    }
}
