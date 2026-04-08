<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Enums\AssetStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory;

class LoadTestingSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();
        
        $this->command->info('Seeding 50 Users...');
        $users = [];
        $timestamp = now()->timestamp;
        for ($i = 0; $i < 50; $i++) {
            $users[] = [
                'id' => (string) Str::uuid(),
                'full_name' => $faker->name,
                'email' => "user{$i}_{$timestamp}_" . Str::random(4) . "@lca.test",
                'password' => Hash::make('password'),
                'role' => $faker->randomElement(['admin', 'technician']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        collect($users)->chunk(10)->each(fn($chunk) => User::insert($chunk->toArray()));
        $userIds = User::pluck('id')->toArray();

        $this->command->info('Seeding 120 Categories...');
        // First 20 as parents
        for ($i = 0; $i < 20; $i++) {
            Category::create([
                'name' => ['en' => "Parent Category {$i} " . Str::random(4), 'ar' => "تصنيف أب {$i} " . Str::random(2)],
            ]);
        }
        $parentIds = Category::pluck('id')->toArray();
        
        // Next 100 as children
        for ($i = 0; $i < 100; $i++) {
            Category::create([
                'name' => ['en' => "Child Category {$i} " . Str::random(4), 'ar' => "تصنيف فرعي {$i} " . Str::random(2)],
                'parent_id' => $faker->randomElement($parentIds),
            ]);
        }
        $categoryIds = Category::pluck('id')->toArray();

        $this->command->info('Seeding 1500 Employees...');
        $employees = [];
        for ($i = 0; $i < 1500; $i++) {
            $employees[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'employee_number' => "LD-EMP-{$i}-" . \Illuminate\Support\Str::random(4),
                'full_name_en' => $faker->name,
                'full_name_ar' => $faker->name,
                'email' => "emp{$i}_{$timestamp}_" . \Illuminate\Support\Str::random(4) . "@lca.test",
                'phone' => $faker->phoneNumber,
                'department' => $faker->word,
                'job_title' => $faker->jobTitle,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if (count($employees) >= 100) {
                Employee::insert($employees);
                $employees = [];
            }
        }
        if (count($employees) > 0) {
            Employee::insert($employees);
        }

        $this->command->info('Seeding 5000 Assets...');
        $assets = [];
        $statuses = AssetStatus::cases();
        for ($i = 0; $i < 5000; $i++) {
            $assets[] = [
                'id' => (string) Str::uuid(),
                'asset_tag' => "TAG-{$timestamp}-" . str_pad($i, 5, '0', STR_PAD_LEFT) . "-" . Str::random(3),
                'serial_number' => "SN-{$timestamp}-" . Str::random(10),
                'name' => json_encode(['en' => "Asset {$i} " . Str::random(4), 'ar' => "أصل {$i} " . Str::random(2)]),
                'category_id' => $faker->randomElement($categoryIds),
                'status' => $faker->randomElement($statuses)->value,
                'manufacturer' => $faker->company,
                'model' => $faker->word,
                'purchase_date' => $faker->date(),
                'purchase_cost' => $faker->randomFloat(2, 100, 5000),
                'created_by' => $faker->randomElement($userIds),
                'created_at' => now(),
                'updated_at' => now(),
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
                'id' => (string) Str::uuid(),
                'asset_id' => $faker->randomElement($assetIds),
                'performed_by' => $faker->randomElement($userIds),
                'action' => $faker->randomElement($actions),
                'performed_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'created_at' => now(),
                'updated_at' => now(),
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
}
