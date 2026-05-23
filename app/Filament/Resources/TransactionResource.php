<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Asset;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Lookups\AssetCondition;
use App\Models\Lookups\AssetReturnReason;
use App\Models\Lookups\Department;
use App\Models\Lookups\MaintenanceType;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\Status;
use App\Models\Lookups\WarrantyProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string { return __('Transaction'); }
    public static function getPluralModelLabel(): string { return __('Transactions'); }
    public static function getNavigationLabel(): string { return __('Transactions'); }

    public static function getNavigationGroup(): ?string
    {
        return __('Asset Management');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'asset_manager']) ?? false;
    }

    protected static function lookupOptions(string $modelClass, ?callable $scope = null): array
    {
        $query = $modelClass::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code');

        if ($scope) {
            $scope($query);
        }

        return $query->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->getTranslatedName()])
            ->toArray();
    }

    protected static function typeOptions(): array
    {
        return [
            Transaction::TYPE_REGISTERED => __('Registered'),
            Transaction::TYPE_CHECK_OUT => __('Check Out'),
            Transaction::TYPE_CHECK_IN => __('Check In'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Transaction'))->columns(2)->schema([
                Forms\Components\TextInput::make('transaction_number')
                    ->label(__('Transaction Number'))
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(__('Auto-generated')),
                Forms\Components\Select::make('type')
                    ->label(__('Type'))
                    ->options(static::typeOptions())
                    ->required()
                    ->default(Transaction::TYPE_CHECK_OUT),
                Forms\Components\Select::make('assignment_status_id')
                    ->label(__('Assignment Status'))
                    ->options(fn () => static::lookupOptions(Status::class, fn ($q) => $q->where('scope', Status::SCOPE_ASSIGNMENT))),
                Forms\Components\Select::make('asset_id')
                    ->label(__('Asset'))
                    ->options(fn () => Asset::query()->orderBy('asset_tag')->limit(200)->get()
                        ->mapWithKeys(fn ($a) => [$a->id => "{$a->asset_tag} — " . $a->getTranslation('name', app()->getLocale())]))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label(__('User'))
                    ->options(fn () => User::where('is_active', true)->orderBy('full_name')->get()
                        ->mapWithKeys(fn ($u) => [$u->id => $u->display_name]))
                    ->searchable(),
                Forms\Components\Select::make('department_id')
                    ->label(__('Department (snapshot)'))
                    ->options(fn () => static::lookupOptions(Department::class)),
                Forms\Components\Select::make('office_location_id')
                    ->label(__('Office Location (snapshot)'))
                    ->options(fn () => static::lookupOptions(OfficeLocation::class)),
                Forms\Components\DateTimePicker::make('checked_out_at')->label(__('Checked Out At')),
                Forms\Components\DateTimePicker::make('checked_in_at')->label(__('Checked In At')),
            ]),

            Forms\Components\Section::make(__('Condition & Return'))->columns(2)->schema([
                Forms\Components\Select::make('condition_out_id')
                    ->label(__('Condition Out'))
                    ->options(fn () => static::lookupOptions(AssetCondition::class)),
                Forms\Components\Select::make('condition_in_id')
                    ->label(__('Condition In'))
                    ->options(fn () => static::lookupOptions(AssetCondition::class)),
                Forms\Components\Select::make('return_reason_id')
                    ->label(__('Return Reason'))
                    ->options(fn () => static::lookupOptions(AssetReturnReason::class)),
            ]),

            Forms\Components\Section::make(__('Maintenance & Warranty'))->columns(2)->schema([
                Forms\Components\Select::make('maintenance_status_id')
                    ->label(__('Maintenance Status'))
                    ->options(fn () => static::lookupOptions(Status::class, fn ($q) => $q->where('scope', Status::SCOPE_MAINTENANCE))),
                Forms\Components\Select::make('maintenance_type_id')
                    ->label(__('Maintenance Type'))
                    ->options(fn () => static::lookupOptions(MaintenanceType::class)),
                Forms\Components\Select::make('warranty_provider_id')
                    ->label(__('Warranty Provider'))
                    ->options(fn () => static::lookupOptions(WarrantyProvider::class)),
            ]),

            Forms\Components\Section::make(__('Notes & Attachment'))->schema([
                Forms\Components\Tabs::make('Translations')->tabs([
                    Forms\Components\Tabs\Tab::make(__('English'))->schema([
                        Forms\Components\Textarea::make('notes.en')->label(__('Notes (EN)')),
                    ]),
                    Forms\Components\Tabs\Tab::make(__('Arabic'))->schema([
                        Forms\Components\Textarea::make('notes.ar')->label(__('Notes (AR)')),
                    ]),
                ])->columnSpanFull(),
                Forms\Components\FileUpload::make('attachment_path')
                    ->label(__('Handover Form'))
                    ->disk('public')
                    ->directory('transactions'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')->label(__('Transaction #'))->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => static::typeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Transaction::TYPE_REGISTERED => 'info',
                        Transaction::TYPE_CHECK_OUT => 'warning',
                        Transaction::TYPE_CHECK_IN => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('asset.asset_tag')->label(__('Asset'))->searchable(),
                Tables\Columns\TextColumn::make('user.display_name')
                    ->label(__('User'))
                    ->searchable(query: fn ($query, string $search) => $query
                        ->whereHas('user', fn ($q) => $q
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('full_name_ar', 'like', "%{$search}%"))),
                Tables\Columns\TextColumn::make('assignmentStatus.code')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->assignmentStatus?->getTranslatedName())
                    ->color(fn ($record) => $record->assignmentStatus?->getColour() ?? 'gray'),
                Tables\Columns\TextColumn::make('assignedBy.display_name')->label(__('Performed By')),
                Tables\Columns\TextColumn::make('checked_out_at')
                    ->label(__('Out'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label(__('In'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('conditionOut.code')
                    ->label(__('Condition Out'))
                    ->formatStateUsing(fn ($state, $record) => $record->conditionOut?->getTranslatedName())
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('conditionIn.code')
                    ->label(__('Condition In'))
                    ->formatStateUsing(fn ($state, $record) => $record->conditionIn?->getTranslatedName())
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options(static::typeOptions()),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('Users'))
                    ->relationship('user', 'full_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                    ->searchable(),
                Tables\Filters\SelectFilter::make('assignment_status_id')
                    ->label(__('Assignment Status'))
                    ->options(fn () => static::lookupOptions(Status::class, fn ($q) => $q->where('scope', Status::SCOPE_ASSIGNMENT))),
                Filter::make('created_at_range')
                    ->label(__('Date Range'))
                    ->form([
                        Forms\Components\Select::make('preset')
                            ->label(__('Preset'))
                            ->options([
                                'last_day' => __('Last 24 hours'),
                                'last_week' => __('Last week'),
                                'last_month' => __('Last month'),
                                'last_year' => __('Last year'),
                                'custom' => __('Custom range'),
                            ])
                            ->live(),
                        Forms\Components\DatePicker::make('from')
                            ->label(__('From'))
                            ->visible(fn ($get) => $get('preset') === 'custom'),
                        Forms\Components\DatePicker::make('to')
                            ->label(__('To'))
                            ->visible(fn ($get) => $get('preset') === 'custom'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $preset = $data['preset'] ?? null;

                        if ($preset === 'last_day') {
                            return $query->where('created_at', '>=', now()->subDay());
                        }
                        if ($preset === 'last_week') {
                            return $query->where('created_at', '>=', now()->subWeek());
                        }
                        if ($preset === 'last_month') {
                            return $query->where('created_at', '>=', now()->subMonth());
                        }
                        if ($preset === 'last_year') {
                            return $query->where('created_at', '>=', now()->subYear());
                        }
                        if ($preset === 'custom') {
                            if (! empty($data['from'])) {
                                $query->whereDate('created_at', '>=', $data['from']);
                            }
                            if (! empty($data['to'])) {
                                $query->whereDate('created_at', '<=', $data['to']);
                            }
                            return $query;
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (! empty($data['preset']) && $data['preset'] !== 'custom') {
                            $indicators[] = __('Range: :preset', ['preset' => str_replace('_', ' ', $data['preset'])]);
                        }
                        if (! empty($data['from'])) {
                            $indicators[] = __('From: :date', ['date' => $data['from']]);
                        }
                        if (! empty($data['to'])) {
                            $indicators[] = __('To: :date', ['date' => $data['to']]);
                        }
                        return $indicators;
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with([
            'asset', 'user', 'assignedBy',
            'assignmentStatus', 'conditionOut', 'conditionIn',
        ]);
    }
}
