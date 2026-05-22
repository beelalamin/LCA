<?php

namespace App\Filament\Pages;

use App\Models\Assignment;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyAssignmentsHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static string $view = 'filament.pages.list-records';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('My Assignments History');
    }

    public function getTitle(): string
    {
        return __('My Assignments History');
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
                Tables\Columns\TextColumn::make('assignment_number')->label(__('Assignment #')),
                Tables\Columns\TextColumn::make('asset.asset_tag')->label(__('Asset')),
                Tables\Columns\TextColumn::make('checked_out_at')->label(__('Out'))->dateTime(),
                Tables\Columns\TextColumn::make('checked_in_at')->label(__('In'))->dateTime(),
                Tables\Columns\TextColumn::make('assignmentStatus.code')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->assignmentStatus?->getTranslatedName()),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->defaultSort('checked_out_at', 'desc');
    }

    protected static function getTableQuery(): Builder
    {
        $employeeId = auth()->user()?->employee_id;

        return Assignment::query()
            ->with(['asset', 'assignmentStatus'])
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->when(! $employeeId, fn ($q) => $q->whereRaw('1 = 0'));
    }
}
