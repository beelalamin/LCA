<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentResource\Pages;
use App\Models\Assignment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function getModelLabel(): string
    {
        return __('Assignment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Assignments');
    }

    public static function canCreate(): bool
    {
        return false; // Created via Check Out action
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset.name')->label(__('Asset'))->searchable(),
                Tables\Columns\TextColumn::make('employee.full_name_en')->label(__('Employee'))->searchable(),
                Tables\Columns\TextColumn::make('assignedBy.full_name')->label(__('Assigned By')),
                Tables\Columns\TextColumn::make('checked_out_at')->label(__('Checked Out At'))->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('checked_in_at')->label(__('Checked In At'))->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('condition_out')->label(__('Condition Out'))->badge(),
                Tables\Columns\TextColumn::make('condition_in')->label(__('Condition In'))->badge(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('employee_id')->relationship('employee', 'full_name_en'),
                Tables\Filters\SelectFilter::make('asset_id')->relationship('asset', 'name'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical')
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAssignments::route('/'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['asset', 'employee', 'assignedBy']);
    }
}
