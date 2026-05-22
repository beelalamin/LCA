<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use App\Models\Lookups\Department;
use App\Models\Lookups\EmploymentType;
use App\Models\Lookups\JobTitle;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('Staff');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Staff');
    }

    public static function getNavigationLabel(): string
    {
        return __('Staff');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Asset Management');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Identity'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('employee_number')
                        ->label(__('Employee Number'))
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\FileUpload::make('photo_path')
                        ->label(__('Photo'))
                        ->image()
                        ->disk('public')
                        ->directory('employees')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('full_name_en')
                        ->label(__('Full Name (EN)'))
                        ->required(),
                    Forms\Components\TextInput::make('full_name_ar')
                        ->label(__('Full Name (AR)')),
                    Forms\Components\TextInput::make('email')
                        ->label(__('Email'))
                        ->email(),
                    Forms\Components\TextInput::make('phone')
                        ->label(__('Phone')),
                ]),

            Forms\Components\Section::make(__('Role & Organization'))
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('department_id')
                        ->label(__('Department'))
                        ->options(fn () => Department::active()->ordered()->get()
                            ->mapWithKeys(fn ($d) => [$d->id => $d->getTranslatedName()]))
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('job_title_id')
                        ->label(__('Job Title'))
                        ->options(fn () => JobTitle::active()->ordered()->get()
                            ->mapWithKeys(fn ($j) => [$j->id => $j->getTranslatedName()]))
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('employment_type_id')
                        ->label(__('Employment Type'))
                        ->options(fn () => EmploymentType::active()->ordered()->get()
                            ->mapWithKeys(fn ($t) => [$t->id => $t->getTranslatedName()]))
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('office_location_id')
                        ->label(__('Office Location'))
                        ->options(fn () => OfficeLocation::active()->ordered()->get()
                            ->mapWithKeys(fn ($l) => [$l->id => $l->getTranslatedName()]))
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('line_manager_id')
                        ->label(__('Line Manager'))
                        ->options(fn () => Employee::query()->where('is_active', true)->pluck('full_name_en', 'id'))
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('status_id')
                        ->label(__('Staff Status'))
                        ->options(fn () => Status::forStaff()->active()->ordered()->get()
                            ->mapWithKeys(fn ($s) => [$s->id => $s->getTranslatedName()]))
                        ->searchable()
                        ->preload(),
                ]),

            Forms\Components\Section::make(__('Employment Dates'))
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('joining_date')->label(__('Joining Date')),
                    Forms\Components\DatePicker::make('leaving_date')->label(__('Leaving Date')),
                ]),

            Forms\Components\Section::make(__('Emergency Contact'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('emergency_contact_name')->label(__('Emergency Contact Name')),
                    Forms\Components\TextInput::make('emergency_contact_phone')->label(__('Emergency Contact Phone')),
                ]),

            Forms\Components\Section::make(__('Other'))
                ->schema([
                    Forms\Components\Textarea::make('notes')->label(__('Notes'))->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')->label(__('Active'))->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label(__('Photo'))
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('employee_number')->label(__('Employee Number'))->searchable(),
                Tables\Columns\TextColumn::make('full_name_en')->label(__('Name'))->searchable(),
                Tables\Columns\TextColumn::make('department.code')
                    ->label(__('Department'))
                    ->formatStateUsing(fn ($state, $record) => $record->department?->getTranslatedName())
                    ->sortable(),
                Tables\Columns\TextColumn::make('jobTitle.code')
                    ->label(__('Job Title'))
                    ->formatStateUsing(fn ($state, $record) => $record->jobTitle?->getTranslatedName())
                    ->toggleable(),
                Tables\Columns\TextColumn::make('officeLocation.code')
                    ->label(__('Location'))
                    ->formatStateUsing(fn ($state, $record) => $record->officeLocation?->getTranslatedName())
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status.code')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->status?->getTranslatedName())
                    ->color(fn ($record) => $record->status?->getColour() ?? 'gray'),
                Tables\Columns\TextColumn::make('active_assignments_count')
                    ->label(__('Active Assignments'))
                    ->counts('activeAssignments'),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')->relationship('department', 'code')->label(__('Department')),
                Tables\Filters\SelectFilter::make('job_title_id')->relationship('jobTitle', 'code')->label(__('Job Title')),
                Tables\Filters\SelectFilter::make('employment_type_id')->relationship('employmentType', 'code')->label(__('Employment Type')),
                Tables\Filters\SelectFilter::make('office_location_id')->relationship('officeLocation', 'code')->label(__('Office Location')),
                Tables\Filters\SelectFilter::make('status_id')->relationship('status', 'code')->label(__('Status')),
                Tables\Filters\TernaryFilter::make('is_active')->label(__('Active')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make(__('Identity'))->schema([
                Grid::make(3)->schema([
                    ImageEntry::make('photo_path')->label(__('Photo'))->disk('public')->circular(),
                    TextEntry::make('employee_number')->label(__('Employee Number')),
                    TextEntry::make('full_name_en')->label(__('Full Name (EN)')),
                    TextEntry::make('full_name_ar')->label(__('Full Name (AR)')),
                    TextEntry::make('email')->label(__('Email')),
                    TextEntry::make('phone')->label(__('Phone')),
                ]),
            ]),
            Section::make(__('Role & Organization'))->schema([
                Grid::make(3)->schema([
                    TextEntry::make('department.code')->label(__('Department'))
                        ->formatStateUsing(fn ($state, $record) => $record->department?->getTranslatedName()),
                    TextEntry::make('jobTitle.code')->label(__('Job Title'))
                        ->formatStateUsing(fn ($state, $record) => $record->jobTitle?->getTranslatedName()),
                    TextEntry::make('employmentType.code')->label(__('Employment Type'))
                        ->formatStateUsing(fn ($state, $record) => $record->employmentType?->getTranslatedName()),
                    TextEntry::make('officeLocation.code')->label(__('Office Location'))
                        ->formatStateUsing(fn ($state, $record) => $record->officeLocation?->getTranslatedName()),
                    TextEntry::make('lineManager.full_name_en')->label(__('Line Manager'))->placeholder('—'),
                    TextEntry::make('status.code')->label(__('Status'))->badge()
                        ->formatStateUsing(fn ($state, $record) => $record->status?->getTranslatedName())
                        ->color(fn ($record) => $record->status?->getColour() ?? 'gray'),
                ]),
            ]),
            Section::make(__('Employment Dates'))->schema([
                Grid::make(2)->schema([
                    TextEntry::make('joining_date')->label(__('Joining Date'))->date(),
                    TextEntry::make('leaving_date')->label(__('Leaving Date'))->date(),
                ]),
            ]),
            Section::make(__('Emergency Contact'))->schema([
                Grid::make(2)->schema([
                    TextEntry::make('emergency_contact_name')->label(__('Emergency Contact Name')),
                    TextEntry::make('emergency_contact_phone')->label(__('Emergency Contact Phone')),
                ]),
            ]),
            Section::make(__('Notes'))->schema([
                TextEntry::make('notes')->label(__('Notes'))->columnSpanFull(),
            ])->collapsed(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['department', 'jobTitle', 'officeLocation', 'status'])
            ->withCount(['activeAssignments']);
    }
}
