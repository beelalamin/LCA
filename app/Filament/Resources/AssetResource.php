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
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    public static function getModelLabel(): string
    {
        return __('Asset');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Assets');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('asset_tag')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(__('Auto-generated on creation')),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('Core Identification'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('Asset Name'))
                                    ->weight('bold')
                                    ->size('lg'),
                                TextEntry::make('asset_tag')
                                    ->label(__('Asset Tag'))
                                    ->copyable()
                                    ->icon('heroicon-m-qr-code')
                                    ->color('primary'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (AssetStatus $state): string => match ($state) {
                                        AssetStatus::PURCHASED => 'gray',
                                        AssetStatus::AVAILABLE => 'info',
                                        AssetStatus::ASSIGNED => 'success',
                                        AssetStatus::IN_REPAIR => 'warning',
                                        AssetStatus::RETIRED, AssetStatus::DISPOSED => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make(__('Technical Details'))
                            ->schema([
                                TextEntry::make('category.name'),
                                TextEntry::make('manufacturer'),
                                TextEntry::make('model'),
                                TextEntry::make('serial_number')
                                    ->copyable()
                                    ->color('info'),
                            ])->columnSpan(1),

                        Section::make(__('Financial & Warranty'))
                            ->schema([
                                TextEntry::make('purchase_date')
                                    ->date(),
                                TextEntry::make('purchase_cost')
                                    ->prefix('$')
                                    ->numeric(),
                                TextEntry::make('warranty_expiry')
                                    ->date()
                                    ->color(fn ($record) => $record->warranty_expiry < now() ? 'danger' : 'success'),
                            ])->columnSpan(1),
                    ]),

                Section::make(__('Location & Ownership'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('location')
                                    ->icon('heroicon-m-map-pin'),
                                TextEntry::make('creator.full_name')
                                    ->label(__('Registered By')),
                            ]),
                    ]),

                Section::make(__('Additional Information'))
                    ->schema([
                        TextEntry::make('notes')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_tag')->label(__('Asset Tag'))->searchable(),
                Tables\Columns\TextColumn::make('serial_number')->label(__('Serial Number'))->searchable(),
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label(__('Category'))->sortable(),
                Tables\Columns\TextColumn::make('manufacturer')->label(__('Manufacturer'))->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (AssetStatus $state): string => match ($state) {
                        AssetStatus::PURCHASED => 'gray',
                        AssetStatus::AVAILABLE => 'info',
                        AssetStatus::ASSIGNED => 'success',
                        AssetStatus::IN_REPAIR => 'warning',
                        AssetStatus::RETIRED, AssetStatus::DISPOSED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('purchase_date')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(fn () => collect(AssetStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->toArray()),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
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
                    Action::make('printBarcode')
                        ->label(__('Print Barcode'))
                        ->icon('heroicon-o-viewfinder-circle')
                        ->url(fn (Asset $record) => route('asset.barcode', $record))
                        ->openUrlInNewTab(),
                    Action::make('printQrCode')
                        ->label(__('Print QR Code'))
                        ->icon('heroicon-o-qr-code')
                        ->url(fn (Asset $record) => route('asset.qrcode', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip(__('Actions'))
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
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'view' => Pages\ViewAsset::route('/{record}'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'creator']);
    }
}
