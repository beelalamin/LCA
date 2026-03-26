<?php

namespace App\Filament\Resources\Shield;

use BezhanSalleh\FilamentShield\Resources\RoleResource as ShieldRoleResource;
use Filament\Facades\Filament;

class RoleResource extends ShieldRoleResource
{
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('System Administration');
    }
}
