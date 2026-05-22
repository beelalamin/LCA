<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Lookups\AssetAssignmentStatus;
use App\Models\Lookups\AssetCondition;
use App\Models\Lookups\Department;
use App\Models\Lookups\Manufacturer;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LookupsSeeder::class);
        $this->call(RolesPermissionsSeeder::class);

        $admin   = $this->makeUser('admin@lc.local',   'Administrator', 'admin');
        $manager = $this->makeUser('manager@lc.local', 'Asset Manager', 'asset_manager');
        $staff   = $this->makeUser('staff@lc.local',   'Staff User',    'user');

        $employees = $this->seedEmployees();

        if (! $staff->employee_id && isset($employees['Angel'])) {
            $staff->update(['employee_id' => $employees['Angel']->id]);
        }

        $this->seedAssetsFromCsv($employees, $admin->id);
    }

    protected function makeUser(string $email, string $name, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'full_name' => $name,
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        return $user;
    }

    /** @return array<string, Employee> keyed by first name */
    protected function seedEmployees(): array
    {
        $adminDept    = Department::where('code', 'admin')->value('id');
        $accountsDept = Department::where('code', 'accounts_hr')->value('id');
        $designDept   = Department::where('code', 'design')->value('id');
        $opsDept      = Department::where('code', 'operations')->value('id');

        $activeStatusId = Status::forStaff()->where('code', 'active')->value('id');

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

        $out = [];
        foreach ($rows as [$num, $en, $ar, $deptId]) {
            $out[$en] = Employee::firstOrCreate(
                ['employee_number' => $num],
                [
                    'full_name_en' => $en,
                    'full_name_ar' => $ar,
                    'department_id' => $deptId,
                    'status_id' => $activeStatusId,
                    'is_active' => true,
                    'joining_date' => Carbon::now()->subYear(),
                ],
            );
        }

        return $out;
    }

    protected function seedAssetsFromCsv(array $employees, string $adminId): void
    {
        $csvPath = base_path('static/Updated_Asset_Management.csv');
        if (! is_file($csvPath)) {
            $this->command->warn("CSV file not found at {$csvPath} — skipping asset import.");
            return;
        }

        $statusMap = [
            'RESERVED'  => Status::forAssets()->where('code', 'reserved')->value('id'),
            'DAMAGED'   => Status::forAssets()->where('code', 'damaged')->value('id'),
            'PURSHAED'  => Status::forAssets()->where('code', 'purchased')->value('id'),
            'PURCHASED' => Status::forAssets()->where('code', 'purchased')->value('id'),
            'AVAILABLE' => Status::forAssets()->where('code', 'available')->value('id'),
        ];

        $goodCondId    = AssetCondition::where('code', 'good')->value('id');
        $damagedCondId = AssetCondition::where('code', 'damaged')->value('id');

        $availableAssignmentId = AssetAssignmentStatus::where('code', 'available')->value('id');
        $assignedAssignmentId  = AssetAssignmentStatus::where('code', 'assigned')->value('id');

        $file = new \SplFileObject($csvPath, 'r');
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_AHEAD);

        $isHeader = true;
        foreach ($file as $row) {
            if (! is_array($row) || $row === [null]) {
                continue;
            }
            if ($isHeader) {
                $isHeader = false;
                continue;
            }
            if (count($row) < 16) {
                $row = array_pad($row, 16, null);
            }

            [$nameEn, $nameAr, $serial, $catStr, $subCatStr, $manuStr, $modelStr,
                $year, $purchasedFrom, $statusStr, $purchaseDate, $purchaseCost,
                $warrantyExpiry, $locationStr, $notesEn, $notesAr] = $row;

            $serial = trim((string) $serial);
            if ($serial === '') {
                continue;
            }
            if (Asset::where('serial_number', $serial)->exists()) {
                continue;
            }

            $statusCode = strtoupper(trim((string) $statusStr));
            $statusId   = $statusMap[$statusCode] ?? $statusMap['AVAILABLE'];
            $condId     = $statusCode === 'DAMAGED' ? $damagedCondId : $goodCondId;

            $nameEn = trim((string) $nameEn);
            if ($nameEn === '') {
                $nameEn = trim((string) $manuStr) ?: 'Unnamed Asset';
            }
            $nameAr = trim((string) $nameAr);

            $assigneeName = $this->extractAssignee((string) $notesEn);
            $assignedEmployee = $assigneeName ? ($employees[$assigneeName] ?? null) : null;

            $assignmentStatusId = $assignedEmployee ? $assignedAssignmentId : $availableAssignmentId;

            [$catId, $subCatId] = $this->resolveCategory($catStr, $subCatStr);

            $description = null;
            $modelClean = trim((string) $modelStr);
            if ($modelClean !== '') {
                $description = ['en' => $modelClean];
            }

            $notes = [];
            $notesEnClean = trim((string) $notesEn);
            $notesArClean = trim((string) $notesAr);
            if ($notesEnClean !== '') $notes['en'] = $notesEnClean;
            if ($notesArClean !== '') $notes['ar'] = $notesArClean;
            $notes = $notes ?: null;

            $yearClean = trim((string) $year);
            $manuYear = ($yearClean !== '' && is_numeric($yearClean)) ? (int) $yearClean : null;

            $costClean = trim((string) $purchaseCost);
            $cost = ($costClean !== '' && is_numeric($costClean)) ? (float) $costClean : null;

            $nameField = ['en' => $nameEn];
            if ($nameAr !== '') {
                $nameField['ar'] = $nameAr;
            }

            $asset = Asset::create([
                'serial_number'        => $serial,
                'name'                 => $nameField,
                'category_id'          => $catId,
                'sub_category_id'      => $subCatId,
                'manufacturer_id'      => $this->resolveManufacturer($manuStr),
                'manufacturer_year'    => $manuYear,
                'status_id'            => $statusId,
                'office_location_id'   => $this->resolveLocation($locationStr),
                'condition_id'         => $condId,
                'purchase_date'        => $this->parseDate($purchaseDate),
                'purchase_cost'        => $cost,
                'warranty_expiry'      => $this->parseDate($warrantyExpiry),
                'description'          => $description,
                'notes'                => $notes,
                'assignment_status_id' => $assignmentStatusId,
                'is_active'            => true,
                'created_by'           => $adminId,
            ]);

            if ($assignedEmployee) {
                Assignment::create([
                    'asset_id'             => $asset->id,
                    'employee_id'          => $assignedEmployee->id,
                    'assigned_by'          => $adminId,
                    'checked_out_at'       => Carbon::now()->subMonths(2),
                    'condition_out_id'     => $condId,
                    'assignment_status_id' => $assignedAssignmentId,
                    'is_active'            => true,
                    'notes'                => $notes,
                ]);
            }
        }
    }

    protected function resolveManufacturer(?string $name): ?string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }
        $code = Str::slug($name, '_');
        if ($code === '') {
            return null;
        }

        return Manufacturer::firstOrCreate(
            ['code' => $code],
            [
                'name' => ['en' => $name],
                'sort_order' => 999,
                'is_active' => true,
            ],
        )->id;
    }

    protected function resolveLocation(?string $name): ?string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }
        $code = Str::slug($name, '_');
        if ($code === '') {
            return null;
        }

        return OfficeLocation::firstOrCreate(
            ['code' => $code],
            [
                'name' => ['en' => $name],
                'sort_order' => 999,
                'is_active' => true,
            ],
        )->id;
    }

    /** @return array{0: ?string, 1: ?string} [categoryId, subCategoryId] */
    protected function resolveCategory(?string $catName, ?string $subName): array
    {
        $catName = trim((string) $catName);
        $subName = trim((string) $subName);

        $parentId = null;
        if ($catName !== '') {
            $parent = Category::query()
                ->whereNull('parent_id')
                ->where('name->en', $catName)
                ->first();

            if (! $parent) {
                $parent = Category::create([
                    'name' => ['en' => $catName],
                    'parent_id' => null,
                ]);
            }
            $parentId = $parent->id;
        }

        $subId = null;
        if ($subName !== '') {
            $sub = Category::query()
                ->where('parent_id', $parentId)
                ->where('name->en', $subName)
                ->first();

            if (! $sub) {
                $sub = Category::create([
                    'name' => ['en' => $subName],
                    'parent_id' => $parentId,
                ]);
            }
            $subId = $sub->id;
        }

        return [$parentId, $subId];
    }

    protected function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function extractAssignee(string $notes): ?string
    {
        $normalised = str_replace(['’', "\xE2\x80\x99"], "'", $notes);
        if (! preg_match("/In\s+([A-Za-z]+)'s\s+use/i", $normalised, $m)) {
            return null;
        }
        return ucfirst(strtolower($m[1]));
    }
}
