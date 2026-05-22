<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class LoadTestingSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();
        $timestamp = now()->timestamp;

        $userRoleId = Role::where('name', 'user')->where('guard_name', 'web')->value('id');
        $adminRoleId = Role::where('name', 'admin')->where('guard_name', 'web')->value('id');

        $this->command->info('Seeding 50 admin/manager Users...');
        $adminUsers = [];
        for ($i = 0; $i < 50; $i++) {
            $adminUsers[] = [
                'id'               => (string) Str::uuid(),
                'full_name'        => $faker->name,
                'email'            => "user{$i}_{$timestamp}_" . Str::random(4) . "@lca.test",
                'password'         => Hash::make('password'),
                'preferred_locale' => 'en',
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }
        collect($adminUsers)->chunk(10)->each(fn ($chunk) => User::insert($chunk->toArray()));

        if ($adminRoleId) {
            $this->assignRoleBulk(array_column($adminUsers, 'id'), $adminRoleId);
        }

        $this->command->info('Seeding 120 Categories...');
        for ($i = 0; $i < 20; $i++) {
            Category::create([
                'name' => ['en' => "Parent Category {$i} " . Str::random(4), 'ar' => "تصنيف أب {$i} " . Str::random(2)],
            ]);
        }
        $parentIds = Category::pluck('id')->toArray();

        for ($i = 0; $i < 100; $i++) {
            Category::create([
                'name' => ['en' => "Child Category {$i} " . Str::random(4), 'ar' => "تصنيف فرعي {$i} " . Str::random(2)],
                'parent_id' => $faker->randomElement($parentIds),
            ]);
        }
        $categoryIds = Category::pluck('id')->toArray();

        $this->command->info('Seeding 1500 staff Users...');
        $staff = [];
        $allStaffIds = [];
        for ($i = 0; $i < 1500; $i++) {
            $id = (string) Str::uuid();
            $allStaffIds[] = $id;
            $staff[] = [
                'id'               => $id,
                'employee_number'  => "LD-EMP-{$i}-" . Str::random(4),
                'full_name'        => $faker->name,
                'full_name_ar'     => $faker->name,
                'email'            => "emp{$i}_{$timestamp}_" . Str::random(4) . "@lca.test",
                'phone'            => $faker->phoneNumber,
                'password'         => Hash::make(Str::random(40)),
                'preferred_locale' => 'en',
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            if (count($staff) >= 100) {
                User::insert($staff);
                $staff = [];
            }
        }
        if (count($staff) > 0) {
            User::insert($staff);
        }

        if ($userRoleId) {
            $this->assignRoleBulk($allStaffIds, $userRoleId);
        }

        $allUserIds = User::pluck('id')->toArray();

        $this->command->info('Seeding 5000 Assets...');
        $assets = [];
        for ($i = 0; $i < 5000; $i++) {
            $assets[] = [
                'id'             => (string) Str::uuid(),
                'asset_tag'      => "TAG-{$timestamp}-" . str_pad($i, 5, '0', STR_PAD_LEFT) . "-" . Str::random(3),
                'serial_number'  => "SN-{$timestamp}-" . Str::random(10),
                'name'           => json_encode(['en' => "Asset {$i} " . Str::random(4), 'ar' => "أصل {$i} " . Str::random(2)]),
                'category_id'    => $faker->randomElement($categoryIds),
                'purchase_date'  => $faker->date(),
                'purchase_cost'  => $faker->randomFloat(2, 100, 5000),
                'created_by'     => $faker->randomElement($allUserIds),
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            if (count($assets) >= 500) {
                Asset::insert($assets);
                $assets = [];
                $this->command->comment("Seeded " . ($i + 1) . " assets...");
            }
        }
        if (count($assets) > 0) {
            Asset::insert($assets);
        }
        $assetIds = Asset::pluck('id')->toArray();

        $this->command->info('Seeding 50000 Activity Logs...');
        $actions = ['REGISTERED', 'CHECKED_OUT', 'CHECKED_IN', 'STATUS_CHANGED', 'MAINTENANCE_SCHEDULED', 'MAINTENANCE_COMPLETED'];
        $logs = [];
        for ($i = 0; $i < 50000; $i++) {
            $logs[] = [
                'id'           => (string) Str::uuid(),
                'asset_id'     => $faker->randomElement($assetIds),
                'performed_by' => $faker->randomElement($allUserIds),
                'action'       => $faker->randomElement($actions),
                'performed_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'created_at'   => now(),
                'updated_at'   => now(),
            ];

            if (count($logs) >= 1000) {
                AuditLog::insert($logs);
                $logs = [];
                $this->command->comment("Seeded " . ($i + 1) . " logs...");
            }
        }
        if (count($logs) > 0) {
            AuditLog::insert($logs);
        }

        $this->command->info('Load Testing Seeding Completed!');
    }

    /** @param array<int, string> $userIds */
    protected function assignRoleBulk(array $userIds, int|string $roleId): void
    {
        $rows = array_map(fn ($id) => [
            'role_id'    => $roleId,
            'model_id'   => $id,
            'model_type' => User::class,
        ], $userIds);

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('model_has_roles')->insert($chunk);
        }
    }
}
