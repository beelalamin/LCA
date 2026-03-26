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

    public static function getNavigationGroup(): ?string
    {
        return __('Asset Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('employee_number')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('full_name_en')->required(),
                Forms\Components\TextInput::make('full_name_ar'),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\TextInput::make('department'),
                Forms\Components\TextInput::make('job_title'),
                Forms\Components\TextInput::make('location'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_number')->searchable(),
                Tables\Columns\TextColumn::make('full_name_en')->searchable(),
                Tables\Columns\TextColumn::make('department')->searchable(),
                Tables\Columns\TextColumn::make('active_assignments_count')
                    ->counts('activeAssignments'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical')
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
            'index' => Pages\ManageEmployees::route('/'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withCount(['activeAssignments']);
    }
}
