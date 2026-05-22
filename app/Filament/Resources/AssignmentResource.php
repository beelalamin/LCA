<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentResource\Pages;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Lookups\AssetAssignmentStatus;
use App\Models\Lookups\AssetCondition;
use App\Models\Lookups\AssetReturnReason;
use App\Models\Lookups\Department;
use App\Models\Lookups\MaintenanceStatus;
use App\Models\Lookups\MaintenanceType;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\WarrantyProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string { return __('Assignment'); }
    public static function getPluralModelLabel(): string { return __('Assignments'); }
    public static function getNavigationLabel(): string { return __('Assignments'); }

    public static function getNavigationGroup(): ?string
    {
        return __('Asset Management');
    }

    protected static function lookupOptions(string $modelClass): array
    {
        return $modelClass::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->getTranslatedName()])
            ->toArray();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Assignment'))->columns(2)->schema([
                Forms\Components\TextInput::make('assignment_number')
                    ->label(__('Assignment Number'))
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(__('Auto-generated')),
                Forms\Components\Select::make('assignment_status_id')
                    ->label(__('Assignment Status'))
                    ->options(fn () => static::lookupOptions(AssetAssignmentStatus::class))
                    ->required(),
                Forms\Components\Select::make('asset_id')
                    ->label(__('Asset'))
                    ->options(fn () => Asset::query()->orderBy('asset_tag')->limit(200)->get()
                        ->mapWithKeys(fn ($a) => [$a->id => "{$a->asset_tag} — " . $a->getTranslation('name', app()->getLocale())]))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('employee_id')
                    ->label(__('Employee'))
                    ->options(fn () => Employee::where('is_active', true)->pluck('full_name_en', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('department_id')
                    ->label(__('Department (snapshot)'))
                    ->options(fn () => static::lookupOptions(Department::class)),
                Forms\Components\Select::make('office_location_id')
                    ->label(__('Office Location (snapshot)'))
                    ->options(fn () => static::lookupOptions(OfficeLocation::class)),
                Forms\Components\DateTimePicker::make('checked_out_at')->label(__('Checked Out At')),
                Forms\Components\DateTimePicker::make('checked_in_at')->label(__('Checked In At')),
                Forms\Components\Toggle::make('is_active')->label(__('Active'))->default(true),
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
                    ->options(fn () => static::lookupOptions(MaintenanceStatus::class)),
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
                    ->directory('assignments'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('assignment_number')->label(__('Assignment #'))->searchable(),
                Tables\Columns\TextColumn::make('asset.asset_tag')->label(__('Asset'))->searchable(),
                Tables\Columns\TextColumn::make('employee.full_name_en')->label(__('Employee'))->searchable(),
                Tables\Columns\TextColumn::make('assignmentStatus.code')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->assignmentStatus?->getTranslatedName())
                    ->color(fn ($record) => $record->assignmentStatus?->getColour() ?? 'gray'),
                Tables\Columns\TextColumn::make('assignedBy.full_name')->label(__('Assigned By')),
                Tables\Columns\TextColumn::make('checked_out_at')->label(__('Out'))->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('checked_in_at')->label(__('In'))->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('conditionOut.code')
                    ->label(__('Condition Out'))
                    ->formatStateUsing(fn ($state, $record) => $record->conditionOut?->getTranslatedName()),
                Tables\Columns\TextColumn::make('conditionIn.code')
                    ->label(__('Condition In'))
                    ->formatStateUsing(fn ($state, $record) => $record->conditionIn?->getTranslatedName()),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('employee_id')->relationship('employee', 'full_name_en'),
                Tables\Filters\SelectFilter::make('asset_id')->relationship('asset', 'asset_tag'),
                Tables\Filters\SelectFilter::make('assignment_status_id')->relationship('assignmentStatus', 'code'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with([
            'asset', 'employee', 'assignedBy',
            'assignmentStatus', 'conditionOut', 'conditionIn',
        ]);
    }
}
