<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getModelLabel(): string
    {
        return __('Employee');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Employees');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('employee_number')
                    ->label(__('Employee Number'))
                    ->required()
                    ->unique(ignoreRecord: true),
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
                Forms\Components\TextInput::make('department')
                    ->label(__('Department')),
                Forms\Components\TextInput::make('job_title')
                    ->label(__('Job Title')),
                Forms\Components\TextInput::make('location')
                    ->label(__('Location')),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_number')->label(__('Employee Number'))->searchable(),
                Tables\Columns\TextColumn::make('full_name_en')->label(__('Name'))->searchable(),
                Tables\Columns\TextColumn::make('phone')->label(__('Phone'))->searchable(),
                Tables\Columns\TextColumn::make('department')->label(__('Department'))->searchable(),
                Tables\Columns\TextColumn::make('job_title')->label(__('Job Title'))->searchable(),
                Tables\Columns\TextColumn::make('active_assignments_count')->label(__('Active Assignments Count'))
                    ->counts('activeAssignments'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical')
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEmployees::route('/'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withCount(['activeAssignments']);
    }
}
