<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $resources = [
            'asset', 'transaction', 'category', 'user',
            'audit::log', 'maintenance::log',
            'lookup::status', 'lookup::manufacturer', 'lookup::model',
            'lookup::supplier', 'lookup::department', 'lookup::job::title',
            'lookup::employment::type', 'lookup::office::location',
            'lookup::asset::condition', 'lookup::warranty::status',
            'lookup::ownership::type', 'lookup::criticality::level',
            'lookup::maintenance::status', 'lookup::disposal::method',
            'lookup::warranty::provider', 'lookup::asset::assignment::status',
            'lookup::asset::return::reason', 'lookup::maintenance::type',
            'lookup::disposal::reason',
        ];

        $prefixes = ['view_any', 'view', 'create', 'update', 'delete', 'delete_any', 'restore', 'restore_any', 'force_delete', 'force_delete_any', 'replicate', 'reorder'];

        $shieldPrefixes = ['view_any', 'view', 'create', 'update', 'delete', 'delete_any'];
        $shieldResources = ['shield::role'];

        $allPermissions = [];

        foreach ($resources as $resource) {
            foreach ($prefixes as $prefix) {
                $allPermissions[] = "{$prefix}_{$resource}";
            }
        }

        foreach ($shieldResources as $resource) {
            foreach ($shieldPrefixes as $prefix) {
                $allPermissions[] = "{$prefix}_{$resource}";
            }
        }

        $pagePermissions = [
            'page_Dashboard',
            'page_Settings',
            'page_Profile',
            'page_BulkImportPage',
            'page_MyAssets',
            'page_MyTransactionsHistory',
        ];

        $allPermissions = array_merge($allPermissions, $pagePermissions);

        foreach ($allPermissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $admin = Role::findOrCreate('admin', 'web');
        $assetManager = Role::findOrCreate('asset_manager', 'web');
        $user = Role::findOrCreate('user', 'web');

        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());

        $assetManagerPermissions = collect($allPermissions)
            ->reject(fn ($p) => str_contains($p, '_user') && ! str_contains($p, '_users'))
            ->reject(fn ($p) => str_contains($p, 'shield::role'))
            ->reject(fn ($p) => str_contains($p, 'audit::log'))
            ->reject(fn ($p) => $p === 'page_Settings')
            ->reject(fn ($p) => str_starts_with($p, 'delete_asset') || str_starts_with($p, 'delete_any_asset') || str_starts_with($p, 'force_delete_asset') || str_starts_with($p, 'force_delete_any_asset'))
            ->values()
            ->all();

        $assetManager->syncPermissions(Permission::whereIn('name', $assetManagerPermissions)->get());

        $userPermissions = [
            'view_asset',
            'view_transaction',
            'page_Dashboard', 'page_Profile',
            'page_MyAssets', 'page_MyTransactionsHistory',
        ];

        $user->syncPermissions(Permission::whereIn('name', $userPermissions)->get());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
