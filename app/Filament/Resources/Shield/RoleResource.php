<?php

namespace App\Filament\Resources\Shield;

use BezhanSalleh\FilamentShield\Resources\RoleResource as ShieldRoleResource;
use Filament\Forms\Form;
use Filament\Tables\Table;

class RoleResource extends ShieldRoleResource
{
    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('Role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Roles');
    }

    public static function getNavigationLabel(): string
    {
        return __('Roles');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System Administration');
    }

    public static function form(Form $form): Form
    {
        $form = parent::form($form);

        $form->schema(static::removeGuardNameFromSchema($form->getComponents()));

        return $form;
    }

    public static function table(Table $table): Table
    {
        $table = parent::table($table);

        $columns = collect($table->getColumns())
            ->reject(fn ($column) => $column->getName() === 'guard_name')
            ->values()
            ->all();

        $table->columns($columns);

        return $table;
    }

    protected static function removeGuardNameFromSchema(array $components): array
    {
        return collect($components)
            ->map(function ($component) {
                if (method_exists($component, 'getChildComponents')) {
                    try {
                        $children = $component->getChildComponents();
                        if (! empty($children)) {
                            $component->schema(static::removeGuardNameFromSchema($children));
                        }
                    } catch (\Throwable $e) {
                        // Some components are not schema containers, skip
                    }
                }

                return $component;
            })
            ->reject(function ($component) {
                if (method_exists($component, 'getName')) {
                    return $component->getName() === 'guard_name';
                }
                return false;
            })
            ->values()
            ->all();
    }
}
