<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Filament\Resources\AssetResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';

    protected static ?string $recordTitleAttribute = 'asset_tag';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Assets in this Category');
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('asset_tag')
            ->columns([
                Tables\Columns\TextColumn::make('asset_tag')
                    ->label(__('Asset Tag'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslation('name', app()->getLocale())),
                Tables\Columns\TextColumn::make('serial_number')
                    ->label(__('Serial'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subCategory.name')
                    ->label(__('SubCategory'))
                    ->formatStateUsing(fn ($state, $record) => $record->subCategory?->getTranslation('name', app()->getLocale()))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status.code')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->status?->getTranslatedName())
                    ->color(fn ($record) => $record->status?->getColour() ?? 'gray'),
                Tables\Columns\TextColumn::make('assignedToUser.display_name')
                    ->label(__('Assigned To'))
                    ->placeholder(__('Not Assigned')),
                Tables\Columns\TextColumn::make('officeLocation.code')
                    ->label(__('Location'))
                    ->formatStateUsing(fn ($state, $record) => $record->officeLocation?->getTranslatedName())
                    ->toggleable(),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => AssetResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([]);
    }
}
