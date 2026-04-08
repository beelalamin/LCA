<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\MaintenanceLog;
use App\Enums\AssetStatus;
use App\Enums\ConditionRating;
use App\Enums\MaintenanceType;
use App\Enums\MaintenanceStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User (if not exists)
        $admin = User::firstOrCreate(
            ['email' => 'admin@lca.local'],
            [
                'id' => (string) Str::uuid(),
                'full_name' => 'Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 2. Create Categories
        $laptops = Category::create(['name' => ['en' => 'Laptops', 'ar' => 'أجهزة لابتوب']]);
        $desktops = Category::create(['name' => ['en' => 'Desktops', 'ar' => 'أجهزة مكتبية']]);
        $monitors = Category::create(['name' => ['en' => 'Monitors', 'ar' => 'شاشات']]);
        $printers = Category::create(['name' => ['en' => 'Printers', 'ar' => 'طابعات']]);

        // 3. Create Employees
        $employees = [
            [
                'employee_number' => 'EMP001',
                'full_name_en' => 'John Doe',
                'full_name_ar' => 'جون دو',
                'email' => 'john.doe@lca.local',
                'department' => ['en' => 'IT', 'ar' => 'تقنية المعلومات'],
                'is_active' => true,
            ],
            [
                'employee_number' => 'EMP002',
                'full_name_en' => 'Sarah Smith',
                'full_name_ar' => 'سارة سميث',
                'email' => 'sarah.smith@lca.local',
                'department' => ['en' => 'HR', 'ar' => 'الموارد البشرية'],
                'is_active' => true,
            ],
            [
                'employee_number' => 'EMP003',
                'full_name_en' => 'Ali Mohamed',
                'full_name_ar' => 'علي محمد',
                'email' => 'ali.m@lca.local',
                'department' => ['en' => 'Finance', 'ar' => 'المالية'],
                'is_active' => true,
            ],
        ];

        foreach ($employees as $empData) {
            Employee::create($empData);
        }

        $allEmployees = Employee::all();

        // 4. Create Assets
        $assets = [
            [
                'name' => ['en' => 'MacBook Pro 16"', 'ar' => 'ماك بوك برو 16 بوصة'],
                'serial_number' => 'MBP2026X01',
                'category_id' => $laptops->id,
                'status' => AssetStatus::ASSIGNED->value,
                'manufacturer' => 'Apple',
                'model' => 'M3 Max',
                'purchase_date' => now()->subMonths(6),
                'purchase_cost' => 3500.00,
                'location' => 'HQ - Level 2',
            ],
            [
                'name' => ['en' => 'Dell XPS 15', 'ar' => 'ديل إكس بي إس 15'],
                'serial_number' => 'DELL7890QR',
                'category_id' => $laptops->id,
                'status' => AssetStatus::AVAILABLE->value,
                'manufacturer' => 'Dell',
                'model' => '9530',
                'purchase_date' => now()->subMonths(12),
                'purchase_cost' => 2200.00,
                'location' => 'HQ - IT Room',
            ],
            [
                'name' => ['en' => 'LG UltraWide 34"', 'ar' => 'إل جي ألترا وايد 34'],
                'serial_number' => 'LG-MON-4455',
                'category_id' => $monitors->id,
                'status' => AssetStatus::AVAILABLE->value,
                'manufacturer' => 'LG',
                'model' => '34WP65G-B',
                'purchase_date' => now()->subMonths(2),
                'purchase_cost' => 450.00,
                'location' => 'HQ - Warehouse',
            ],
            [
                'name' => ['en' => 'HP LaserJet Pro', 'ar' => 'إتش بي ليزر جيت برو'],
                'serial_number' => 'HP-PRNT-1122',
                'category_id' => $printers->id,
                'status' => AssetStatus::IN_REPAIR->value,
                'manufacturer' => 'HP',
                'model' => 'M404dn',
                'purchase_date' => now()->subYears(2),
                'purchase_cost' => 300.00,
                'location' => 'External Service Center',
            ],
        ];

        foreach ($assets as $assetData) {
            Asset::create(array_merge($assetData, ['created_by' => $admin->id]));
        }

        $allAssets = Asset::all();

        // 5. Create active assignments for ASSIGNED assets
        $assignedAsset = Asset::where('status', AssetStatus::ASSIGNED->value)->first();
        if ($assignedAsset) {
            Assignment::create([
                'asset_id' => $assignedAsset->id,
                'employee_id' => $allEmployees->first()->id,
                'assigned_by' => $admin->id,
                'checked_out_at' => now()->subMonths(5),
                'condition_out' => ConditionRating::GOOD->value,
                'is_active' => true,
                'notes' => 'Primary workstation assigned upon joining.',
            ]);
        }

        // 6. Create Maintenance Logs
        $repairAsset = Asset::where('status', AssetStatus::IN_REPAIR->value)->first();
        if ($repairAsset) {
            MaintenanceLog::create([
                'asset_id' => $repairAsset->id,
                'technician_id' => $admin->id,
                'type' => MaintenanceType::REPAIR->value,
                'status' => MaintenanceStatus::IN_PROGRESS->value,
                'description' => 'Replacing fuser unit and paper rollers.',
                'scheduled_date' => now()->subDays(2),
                'cost' => 120.00,
                'vendor' => 'HP Authorized Service',
            ]);
        }

        // 7. Completed Maintenance Log (History)
        $availableAsset = Asset::where('status', AssetStatus::AVAILABLE->value)->first();
        if ($availableAsset) {
            MaintenanceLog::create([
                'asset_id' => $availableAsset->id,
                'technician_id' => $admin->id,
                'type' => MaintenanceType::INSPECTION->value,
                'status' => MaintenanceStatus::COMPLETED->value,
                'description' => 'Regular health check and cleaning.',
                'scheduled_date' => now()->subMonths(3),
                'completed_date' => now()->subMonths(3)->addDays(1),
                'cost' => 0,
            ]);
        }
    }
}
