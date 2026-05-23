<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Lookups\AssetCondition;
use App\Models\Lookups\AssetModel;
use App\Models\Lookups\AssetReturnReason;
use App\Models\Lookups\CriticalityLevel;
use App\Models\Lookups\Department;
use App\Models\Lookups\DisposalMethod;
use App\Models\Lookups\DisposalReason;
use App\Models\Lookups\MaintenanceType;
use App\Models\Lookups\Manufacturer;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\OwnershipType;
use App\Models\Lookups\Status;
use App\Models\Lookups\Supplier;
use App\Models\Lookups\WarrantyProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Grid as IGrid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as ISection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string { return __('Asset'); }
    public static function getPluralModelLabel(): string { return __('Assets'); }
    public static function getNavigationLabel(): string { return __('Assets'); }

    public static function getNavigationGroup(): ?string
    {
        return __('Asset Management');
    }

    protected static function lookupOptions(string $modelClass, ?callable $scope = null): array
    {
        $query = $modelClass::query()->where('is_active', true)->orderBy('sort_order')->orderBy('code');

        if ($scope) {
            $scope($query);
        }

        return $query->get()->mapWithKeys(fn ($r) => [$r->id => $r->getTranslatedName()])->toArray();
    }

    public static function identificationSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('asset_tag')
                    ->label(__('Asset Tag'))
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(__('Auto-generated on creation')),
                Forms\Components\TextInput::make('serial_number')
                    ->label(__('Serial Number'))
                    ->unique(ignoreRecord: true),
            ]),
            Forms\Components\Tabs::make('translations')->tabs([
                Forms\Components\Tabs\Tab::make(__('English'))->schema([
                    Forms\Components\TextInput::make('name.en')->required()->label(__('Name (EN)')),
                    Forms\Components\Textarea::make('description.en')->label(__('Description (EN)')),
                    Forms\Components\Textarea::make('notes.en')->label(__('Notes (EN)')),
                ]),
                Forms\Components\Tabs\Tab::make(__('Arabic'))->schema([
                    Forms\Components\TextInput::make('name.ar')->label(__('Name (AR)')),
                    Forms\Components\Textarea::make('description.ar')->label(__('Description (AR)')),
                    Forms\Components\Textarea::make('notes.ar')->label(__('Notes (AR)')),
                ]),
            ])->columnSpanFull(),
            Forms\Components\FileUpload::make('image_path')
                ->label(__('Asset Image'))
                ->image()
                ->disk('public')
                ->directory('assets')
                ->maxSize(10240)
                ->imageResizeMode('contain')
                ->imageResizeTargetWidth('1280')
                ->imageResizeTargetHeight('1280')
                ->imageEditor(),
        ];
    }

    public static function specsSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('category_id')
                    ->label(__('Category'))
                    ->options(fn () => Category::query()->whereNull('parent_id')->get()
                        ->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('name', app()->getLocale())]))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('sub_category_id', null)),
                Forms\Components\Select::make('sub_category_id')
                    ->label(__('SubCategory'))
                    ->options(function (Get $get) {
                        if (! $get('category_id')) return [];
                        return Category::query()
                            ->where('parent_id', $get('category_id'))
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('name', app()->getLocale())]);
                    })
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('manufacturer_id')
                    ->label(__('Manufacturer'))
                    ->options(fn () => static::lookupOptions(Manufacturer::class))
                    ->searchable()
                    ->preload()
                    ->live(),
                Forms\Components\Select::make('model_id')
                    ->label(__('Model'))
                    ->options(function (Get $get) {
                        $query = AssetModel::query()->where('is_active', true);
                        if ($get('manufacturer_id')) {
                            $query->where('manufacturer_id', $get('manufacturer_id'));
                        }
                        return $query->orderBy('sort_order')->orderBy('code')
                            ->get()->mapWithKeys(fn ($m) => [$m->id => $m->getTranslatedName()]);
                    })
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('manufacturer_year')
                    ->label(__('Manufacturer Year'))
                    ->numeric()
                    ->minValue(1980)
                    ->maxValue((int) date('Y') + 1),
                Forms\Components\Select::make('condition_id')
                    ->label(__('Condition'))
                    ->options(fn () => static::lookupOptions(AssetCondition::class))
                    ->searchable(),
                Forms\Components\Select::make('criticality_id')
                    ->label(__('Criticality'))
                    ->options(fn () => static::lookupOptions(CriticalityLevel::class))
                    ->searchable(),
                Forms\Components\Select::make('status_id')
                    ->label(__('Status'))
                    ->options(fn () => static::lookupOptions(Status::class, fn ($q) => $q->where('scope', 'asset')))
                    ->searchable()
                    ->required(),
            ]),
        ];
    }

    public static function financialSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('supplier_id')
                    ->label(__('Supplier / Store'))
                    ->options(fn () => static::lookupOptions(Supplier::class))
                    ->searchable(),
                Forms\Components\Select::make('ownership_type_id')
                    ->label(__('Ownership'))
                    ->options(fn () => static::lookupOptions(OwnershipType::class))
                    ->searchable(),
                Forms\Components\TextInput::make('invoice_number')->label(__('Invoice Number')),
                Forms\Components\TextInput::make('purchase_order_number')->label(__('Purchase Order Number')),
                Forms\Components\DatePicker::make('purchase_date')->label(__('Purchase Date')),
                Forms\Components\TextInput::make('purchase_cost')->label(__('Purchase Cost'))->numeric()->prefix('QAR'),
                Forms\Components\DatePicker::make('warranty_expiry')->label(__('Warranty Expiry')),
                Forms\Components\Select::make('warranty_status_id')
                    ->label(__('Warranty Status'))
                    ->options(fn () => static::lookupOptions(Status::class, fn ($q) => $q->where('scope', Status::SCOPE_WARRANTY)))
                    ->searchable(),
                Forms\Components\Select::make('warranty_provider_id')
                    ->label(__('Warranty Provider'))
                    ->options(fn () => static::lookupOptions(WarrantyProvider::class))
                    ->searchable(),
            ]),
        ];
    }

    public static function locationSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('office_location_id')
                    ->label(__('Office Location'))
                    ->options(fn () => static::lookupOptions(OfficeLocation::class))
                    ->searchable(),
                Forms\Components\Select::make('department_id')
                    ->label(__('Department'))
                    ->options(fn () => static::lookupOptions(Department::class))
                    ->searchable(),
            ]),
        ];
    }

    public static function assignmentSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('assignment_status_id')
                    ->label(__('Assignment Status'))
                    ->options(fn () => static::lookupOptions(Status::class, fn ($q) => $q->where('scope', Status::SCOPE_ASSIGNMENT)))
                    ->searchable(),
                Forms\Components\Select::make('assigned_to_user_id')
                    ->label(__('Assigned To'))
                    ->options(fn () => User::where('is_active', true)->orderBy('full_name')->pluck('full_name', 'id'))
                    ->searchable(),
                Forms\Components\DatePicker::make('assigned_date')->label(__('Assigned Date')),
                Forms\Components\DatePicker::make('return_date')->label(__('Return Date')),
                Forms\Components\Select::make('return_reason_id')
                    ->label(__('Return Reason'))
                    ->options(fn () => static::lookupOptions(AssetReturnReason::class))
                    ->searchable(),
            ]),
        ];
    }

    public static function maintenanceSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('maintenance_status_id')
                    ->label(__('Maintenance Status'))
                    ->options(fn () => static::lookupOptions(Status::class, fn ($q) => $q->where('scope', Status::SCOPE_MAINTENANCE)))
                    ->searchable(),
                Forms\Components\Select::make('maintenance_type_id')
                    ->label(__('Maintenance Type'))
                    ->options(fn () => static::lookupOptions(MaintenanceType::class))
                    ->searchable(),
                Forms\Components\DatePicker::make('last_maintenance_date')->label(__('Last Maintenance')),
                Forms\Components\DatePicker::make('next_maintenance_date')->label(__('Next Maintenance')),
            ]),
        ];
    }

    public static function disposalSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\DatePicker::make('disposal_date')->label(__('Disposal Date')),
                Forms\Components\Select::make('disposal_method_id')
                    ->label(__('Disposal Method'))
                    ->options(fn () => static::lookupOptions(DisposalMethod::class))
                    ->searchable(),
                Forms\Components\Select::make('disposal_reason_id')
                    ->label(__('Disposal Reason'))
                    ->options(fn () => static::lookupOptions(DisposalReason::class))
                    ->searchable(),
            ]),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('asset_tabs')
                ->columnSpanFull()
                ->tabs([
                    Forms\Components\Tabs\Tab::make(__('Identification'))->schema(static::identificationSchema()),
                    Forms\Components\Tabs\Tab::make(__('Specs'))->schema(static::specsSchema()),
                    Forms\Components\Tabs\Tab::make(__('Financial & Warranty'))->schema(static::financialSchema()),
                    Forms\Components\Tabs\Tab::make(__('Location'))->schema(static::locationSchema()),
                    Forms\Components\Tabs\Tab::make(__('Assignment'))->schema(static::assignmentSchema()),
                    Forms\Components\Tabs\Tab::make(__('Maintenance'))->schema(static::maintenanceSchema()),
                    Forms\Components\Tabs\Tab::make(__('Disposal'))->schema(static::disposalSchema()),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            ISection::make(__('Core Identification'))->schema([
                IGrid::make(3)->schema([
                    TextEntry::make('name')->label(__('Asset Name'))->weight('bold')->size('lg'),
                    TextEntry::make('asset_tag')->label(__('Asset Tag'))->copyable()->icon('heroicon-m-qr-code')->color('primary'),
                    TextEntry::make('status.code')
                        ->label(__('Status'))
                        ->badge()
                        ->formatStateUsing(fn ($state, $record) => $record->status?->getTranslatedName())
                        ->color(fn ($record) => $record->status?->getColour() ?? 'gray'),
                    TextEntry::make('serial_number')->label(__('Serial Number'))->copyable(),
                    TextEntry::make('manufacturer.code')
                        ->label(__('Manufacturer'))
                        ->formatStateUsing(fn ($state, $record) => $record->manufacturer?->getTranslatedName()),
                    TextEntry::make('assetModel.code')
                        ->label(__('Model'))
                        ->formatStateUsing(fn ($state, $record) => $record->assetModel?->getTranslatedName()),
                    ImageEntry::make('image_path')->label(__('Image'))->disk('public'),
                ]),
            ]),

            ISection::make(__('Financial & Warranty'))->schema([
                IGrid::make(3)->schema([
                    TextEntry::make('purchase_date')->label(__('Purchase Date'))->date(),
                    TextEntry::make('purchase_cost')->label(__('Purchase Cost'))->prefix('QAR ')->numeric(),
                    TextEntry::make('warranty_expiry')->label(__('Warranty Expiry'))->date()
                        ->color(fn ($record) => $record->warranty_expiry && $record->warranty_expiry < now() ? 'danger' : 'success'),
                    TextEntry::make('warrantyStatus.code')->label(__('Warranty Status'))
                        ->formatStateUsing(fn ($state, $record) => $record->warrantyStatus?->getTranslatedName()),
                    TextEntry::make('warrantyProvider.code')->label(__('Warranty Provider'))
                        ->formatStateUsing(fn ($state, $record) => $record->warrantyProvider?->getTranslatedName()),
                    TextEntry::make('supplier.code')->label(__('Supplier'))
                        ->formatStateUsing(fn ($state, $record) => $record->supplier?->getTranslatedName()),
                ]),
            ]),

            ISection::make(__('Location & Ownership'))->schema([
                IGrid::make(3)->schema([
                    TextEntry::make('officeLocation.code')->label(__('Office Location'))
                        ->formatStateUsing(fn ($state, $record) => $record->officeLocation?->getTranslatedName()),
                    TextEntry::make('department.code')->label(__('Department'))
                        ->formatStateUsing(fn ($state, $record) => $record->department?->getTranslatedName()),
                    TextEntry::make('ownershipType.code')->label(__('Ownership'))
                        ->formatStateUsing(fn ($state, $record) => $record->ownershipType?->getTranslatedName()),
                ]),
            ]),

            ISection::make(__('Notes'))->schema([
                TextEntry::make('notes')->label(__('Notes'))->markdown()->columnSpanFull(),
                TextEntry::make('description')->label(__('Description'))->markdown()->columnSpanFull(),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label(__('Photo'))
                    ->disk('public')
                    ->square()
                    ->size(40)
                    ->defaultImageUrl(fn () => 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%23999" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg>')),
                Tables\Columns\TextColumn::make('asset_tag')->label(__('Asset Tag'))->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslation('name', app()->getLocale())),
                Tables\Columns\TextColumn::make('serial_number')->label(__('Serial'))->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $record->category?->getTranslation('name', app()->getLocale(), true)),
                Tables\Columns\TextColumn::make('manufacturer.code')
                    ->label(__('Manufacturer'))
                    ->formatStateUsing(fn ($state, $record) => $record->manufacturer?->getTranslatedName())
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status.code')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->status?->getTranslatedName())
                    ->color(fn ($record) => $record->status?->getColour() ?? 'gray'),
                Tables\Columns\TextColumn::make('assignedToUser.full_name')
                    ->label(__('Assigned To'))
                    ->placeholder(__('Not Assigned')),
                Tables\Columns\TextColumn::make('officeLocation.code')
                    ->label(__('Location'))
                    ->formatStateUsing(fn ($state, $record) => $record->officeLocation?->getTranslatedName())
                    ->toggleable(),
                Tables\Columns\TextColumn::make('warranty_expiry')->label(__('Warranty Expiry'))->date()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('purchase_date')->label(__('Purchase Date'))->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->label(__('Status'))
                    ->relationship('status', 'code'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('manufacturer_id')
                    ->label(__('Manufacturer'))
                    ->relationship('manufacturer', 'code'),
                Tables\Filters\SelectFilter::make('assignment_status_id')
                    ->label(__('Assignment Status'))
                    ->relationship('assignmentStatus', 'code'),
                Tables\Filters\SelectFilter::make('office_location_id')
                    ->label(__('Office Location'))
                    ->relationship('officeLocation', 'code'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Action::make('checkOut')
                        ->label(__('Check Out'))
                        ->icon('heroicon-o-arrow-right-start-on-rectangle')
                        ->color('success')
                        ->visible(fn (Asset $record) => in_array($record->status?->code, ['available', 'purchased', 'reserved']))
                        ->form([
                            Forms\Components\Select::make('user_id')
                                ->label(__('User'))
                                ->options(User::where('is_active', true)->orderBy('full_name')->pluck('full_name', 'id'))
                                ->required()
                                ->searchable(),
                            Forms\Components\Select::make('condition_out_id')
                                ->label(__('Condition'))
                                ->options(fn () => static::lookupOptions(AssetCondition::class))
                                ->required(),
                            Forms\Components\Textarea::make('notes_en')->label(__('Notes (EN)')),
                        ])
                        ->action(function (Asset $record, array $data) {
                            $assignedStatusId = Status::forAssignment()->where('code', 'assigned')->value('id');
                            $assetAssignedStatusId = Status::forAssets()->where('code', 'assigned')->value('id');

                            Transaction::create([
                                'asset_id' => $record->id,
                                'type' => Transaction::TYPE_CHECK_OUT,
                                'user_id' => $data['user_id'],
                                'assigned_by' => auth()->id(),
                                'condition_out_id' => $data['condition_out_id'],
                                'checked_out_at' => now(),
                                'assignment_status_id' => $assignedStatusId,
                                'notes' => ['en' => $data['notes_en'] ?? null],
                            ]);

                            $record->update(['status_id' => $assetAssignedStatusId]);

                            Notification::make()->title(__('Asset checked out successfully'))->success()->send();
                        }),
                    Action::make('checkIn')
                        ->label(__('Check In'))
                        ->icon('heroicon-o-arrow-left-start-on-rectangle')
                        ->color('warning')
                        ->visible(fn (Asset $record) => $record->status?->code === 'assigned')
                        ->form([
                            Forms\Components\Select::make('condition_in_id')
                                ->label(__('Return Condition'))
                                ->options(fn () => static::lookupOptions(AssetCondition::class))
                                ->required(),
                            Forms\Components\Select::make('return_reason_id')
                                ->label(__('Return Reason'))
                                ->options(fn () => static::lookupOptions(AssetReturnReason::class)),
                            Forms\Components\Textarea::make('notes_en')->label(__('Notes (EN)')),
                        ])
                        ->action(function (Asset $record, array $data) {
                            $returnedStatusId = Status::forAssignment()->where('code', 'returned')->value('id');
                            $availableStatusId = Status::forAssets()->where('code', 'available')->value('id');
                            $openCheckOut = $record->activeAssignment;

                            Transaction::create([
                                'asset_id' => $record->id,
                                'type' => Transaction::TYPE_CHECK_IN,
                                'user_id' => $openCheckOut?->user_id,
                                'assigned_by' => auth()->id(),
                                'condition_in_id' => $data['condition_in_id'],
                                'return_reason_id' => $data['return_reason_id'] ?? null,
                                'checked_in_at' => now(),
                                'assignment_status_id' => $returnedStatusId,
                                'notes' => ['en' => $data['notes_en'] ?? null],
                            ]);

                            $openCheckOut?->update(['checked_in_at' => now()]);

                            $record->update(['status_id' => $availableStatusId]);

                            Notification::make()->title(__('Asset checked in successfully'))->success()->send();
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
                ])->icon('heroicon-m-ellipsis-vertical')->tooltip(__('Actions')),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkPrint')
                        ->label(__('Print Labels'))
                        ->icon('heroicon-o-printer')
                        ->action(fn (\Illuminate\Support\Collection $records) =>
                            redirect()->route('asset.bulk-labels', ['ids' => $records->pluck('id')->toArray()])),
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
        return parent::getEloquentQuery()
            ->with([
                'category', 'subCategory', 'manufacturer', 'assetModel',
                'status', 'assignmentStatus', 'officeLocation', 'department',
                'assignedToUser', 'warrantyStatus', 'warrantyProvider', 'supplier',
                'condition', 'criticality', 'ownershipType',
                'maintenanceStatus', 'maintenanceType',
                'disposalMethod', 'disposalReason',
            ]);
    }
}
