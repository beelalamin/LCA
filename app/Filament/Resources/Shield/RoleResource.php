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

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        $form = parent::form($form);

        static::hideGuardNameInSchema($form->getComponents());

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

    protected static function hideGuardNameInSchema(array $components): void
    {
        foreach ($components as $component) {
            if (method_exists($component, 'getName') && $component->getName() === 'guard_name') {
                if (method_exists($component, 'hidden')) {
                    $component->hidden(true);
                }
                if (method_exists($component, 'dehydrated')) {
                    $component->dehydrated(false);
                }
                continue;
            }

            if (method_exists($component, 'getChildComponents')) {
                try {
                    $children = $component->getChildComponents();
                    if (! empty($children)) {
                        static::hideGuardNameInSchema($children);
                    }
                } catch (\Throwable $e) {
                    // Skip non-container components
                }
            }
        }
    }
}
