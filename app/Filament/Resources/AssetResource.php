<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Filament\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;
use App\Enums\AssetStatus;
use App\Enums\ConditionRating;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('asset_tag')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Auto-generated on creation'),
                Forms\Components\TextInput::make('serial_number')
                    ->unique(ignoreRecord: true),
                Forms\Components\Tabs::make('Translations')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\TextInput::make('name.en')
                                    ->required()
                                    ->label(__('Name (EN)')),
                                Forms\Components\Textarea::make('notes.en')
                                    ->label(__('Notes (EN)')),
                            ]),
                        Forms\Components\Tabs\Tab::make('Arabic')
                            ->schema([
                                Forms\Components\TextInput::make('name.ar')
                                    ->label(__('Name (AR)')),
                                Forms\Components\Textarea::make('notes.ar')
                                    ->label(__('Notes (AR)')),
                            ]),
                    ])->columnSpanFull(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('manufacturer'),
                Forms\Components\TextInput::make('model'),
                Forms\Components\Select::make('status')
                    ->options(\App\Enums\AssetStatus::class)
                    ->default(\App\Enums\AssetStatus::PURCHASED->value),
                Forms\Components\DatePicker::make('purchase_date'),
                Forms\Components\TextInput::make('purchase_cost')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\DatePicker::make('warranty_expiry'),
                Forms\Components\TextInput::make('location'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_tag')->searchable(),
                Tables\Columns\TextColumn::make('serial_number')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->sortable(),
                Tables\Columns\TextColumn::make('manufacturer')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PURCHASED' => 'gray',
                        'AVAILABLE' => 'info',
                        'ASSIGNED' => 'success',
                        'IN_REPAIR' => 'warning',
                        'RETIRED', 'DISPOSED' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('purchase_date')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(\App\Enums\AssetStatus::class),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('checkOut')
                    ->label(__('Check Out'))
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->color('success')
                    ->visible(fn (Asset $record) => $record->status === AssetStatus::AVAILABLE->value)
                    ->form([
                        Forms\Components\Select::make('employee_id')
                            ->label(__('Employee'))
                            ->options(Employee::where('is_active', true)->pluck('full_name_en', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('condition_out')
                            ->label(__('Condition'))
                            ->options(ConditionRating::class)
                            ->default(ConditionRating::GOOD->value)
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                    ])
                    ->action(function (Asset $record, array $data) {
                        Assignment::create([
                            'asset_id' => $record->id,
                            'employee_id' => $data['employee_id'],
                            'assigned_by' => auth()->id(),
                            'condition_out' => $data['condition_out'],
                            'checked_out_at' => now(),
                            'notes' => $data['notes'],
                            'is_active' => true,
                        ]);

                        $record->update(['status' => AssetStatus::ASSIGNED->value]);

                        Notification::make()
                            ->title(__('Asset checked out successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('checkIn')
                    ->label(__('Check In'))
                    ->icon('heroicon-o-arrow-left-start-on-rectangle')
                    ->color('warning')
                    ->visible(fn (Asset $record) => $record->status === AssetStatus::ASSIGNED->value)
                    ->form([
                        Forms\Components\Select::make('condition_in')
                            ->label(__('Return Condition'))
                            ->options(ConditionRating::class)
                            ->default(ConditionRating::GOOD->value)
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                    ])
                    ->action(function (Asset $record, array $data) {
                        $assignment = $record->activeAssignment;
                        
                        if ($assignment) {
                            $assignment->update([
                                'checked_in_at' => now(),
                                'condition_in' => $data['condition_in'],
                                'notes' => $data['notes'],
                                'is_active' => false,
                            ]);
                        }

                        $record->update(['status' => AssetStatus::AVAILABLE->value]);

                        Notification::make()
                            ->title(__('Asset checked in successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('printLabel')
                    ->label(__('Print Label'))
                    ->icon('heroicon-o-printer')
                    ->url(fn (Asset $record) => route('asset.label', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkPrint')
                        ->label(__('Print Labels'))
                        ->icon('heroicon-o-printer')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            // In a real app, this would merge PDFs. 
                            // For simplicity, we can redirect or show link.
                            return redirect()->route('asset.label', ['asset' => $records->first()->id]);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAssets::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'creator']);
    }
}
