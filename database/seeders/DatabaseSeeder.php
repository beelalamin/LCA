<?php

namespace Database\Seeders;

use App\Models\Lookups\Department;
use App\Models\Lookups\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LookupsSeeder::class);
        $this->call(RolesPermissionsSeeder::class);

        $this->makeAccount('admin@luxurycode.qa',   'Administrator', 'admin');
        $this->makeAccount('manager@luxurycode.qa', 'Asset Manager', 'asset_manager');

        $this->seedStaffUsers();

        $this->call(AssetsSeeder::class);
    }

    protected function makeAccount(string $email, string $name, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'full_name' => $name,
                'password' => Hash::make('password'),
                'is_active' => true,
                'preferred_locale' => 'en',
            ],
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        return $user;
    }

    protected function seedStaffUsers(): void
    {
        $adminDept    = Department::where('code', 'admin')->value('id');
        $accountsDept = Department::where('code', 'accounts_hr')->value('id');
        $designDept   = Department::where('code', 'design')->value('id');
        $opsDept      = Department::where('code', 'operations')->value('id');

        $activeStatusId = Status::forUsers()->where('code', 'active')->value('id');

        $rows = [
            ['EMP-001', 'Angel',     'أنجل',     $adminDept],
            ['EMP-002', 'Abdalla',   'عبدالله',   $accountsDept],
            ['EMP-003', 'Shaikha',   'شيخة',     $designDept],
            ['EMP-004', 'Dana',      'دانا',     $designDept],
            ['EMP-005', 'Nadeen',    'نادين',    $designDept],
            ['EMP-006', 'Afsal',     'أفصل',     $designDept],
            ['EMP-007', 'Anbu',      'أنبو',     $designDept],
            ['EMP-008', 'Montassar', 'منتصر',    $designDept],
            ['EMP-009', 'Tixan',     'تيكسان',   $designDept],
            ['EMP-010', 'Thabet',    'ثابت',     $opsDept],
        ];

        foreach ($rows as [$num, $en, $ar, $deptId]) {
            $user = User::firstOrCreate(
                ['employee_number' => $num],
                [
                    'full_name'        => $en,
                    'full_name_ar'     => $ar,
                    'email'            => strtolower($en) . '@luxurycode.qa',
                    'password'         => Hash::make('password'),
                    'preferred_locale' => 'en',
                    'department_id'    => $deptId,
                    'status_id'        => $activeStatusId,
                    'is_active'        => true,
                    'joining_date'     => Carbon::now()->subYear(),
                ],
            );

            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }
        }
    }
}
