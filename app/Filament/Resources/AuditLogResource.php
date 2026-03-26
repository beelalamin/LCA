<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('performed_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('action')->badge(),
                Tables\Columns\TextColumn::make('entity_type')->searchable(),
                Tables\Columns\TextColumn::make('asset.name')->searchable(),
                Tables\Columns\TextColumn::make('performedBy.full_name')->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'REGISTERED' => 'REGISTERED',
                        'CHECKED_OUT' => 'CHECKED_OUT',
                        'CHECKED_IN' => 'CHECKED_IN',
                        'STATUS_CHANGED' => 'STATUS_CHANGED',
                        'BULK_IMPORTED' => 'BULK_IMPORTED',
                        'LABEL_PRINTED' => 'LABEL_PRINTED'
                    ]),
                Tables\Filters\SelectFilter::make('performed_by')
                    ->relationship('performedBy', 'full_name'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical')
            ])
            ->bulkActions([])
            ->defaultSort('performed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAuditLogs::route('/'),
        ];
    }
}
