<?php

namespace Database\Seeders;

use App\Models\Lookups\AssetAssignmentStatus;
use App\Models\Lookups\AssetCondition;
use App\Models\Lookups\AssetReturnReason;
use App\Models\Lookups\CriticalityLevel;
use App\Models\Lookups\Department;
use App\Models\Lookups\DisposalMethod;
use App\Models\Lookups\DisposalReason;
use App\Models\Lookups\EmploymentType;
use App\Models\Lookups\JobTitle;
use App\Models\Lookups\MaintenanceStatus;
use App\Models\Lookups\MaintenanceType;
use App\Models\Lookups\Manufacturer;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\OwnershipType;
use App\Models\Lookups\Status;
use App\Models\Lookups\Supplier;
use App\Models\Lookups\WarrantyProvider;
use App\Models\Lookups\WarrantyStatus;
use Illuminate\Database\Seeder;

class LookupsSeeder extends Seeder
{
    public function run(): void
    {
        $assetStatuses = [
            ['code' => 'purchased',  'name' => ['en' => 'Purchased',  'ar' => 'تم الشراء']],
            ['code' => 'available',  'name' => ['en' => 'Available',  'ar' => 'متاح']],
            ['code' => 'reserved',   'name' => ['en' => 'Reserved',   'ar' => 'محجوز']],
            ['code' => 'assigned',   'name' => ['en' => 'Assigned',   'ar' => 'تم الصرف']],
            ['code' => 'in_repair',  'name' => ['en' => 'In Repair',  'ar' => 'في الصيانة']],
            ['code' => 'damaged',    'name' => ['en' => 'Damaged',    'ar' => 'تالف']],
            ['code' => 'retired',    'name' => ['en' => 'Retired',    'ar' => 'مكهن']],
            ['code' => 'disposed',   'name' => ['en' => 'Disposed',   'ar' => 'تم التصرف فيه']],
            ['code' => 'lost',       'name' => ['en' => 'Lost',       'ar' => 'مفقود']],
        ];

        foreach ($assetStatuses as $i => $row) {
            Status::firstOrCreate(
                ['code' => $row['code']],
                array_merge($row, ['scope' => 'asset', 'sort_order' => $i * 10])
            );
        }

        $staffStatuses = [
            ['code' => 'active',     'name' => ['en' => 'Active',     'ar' => 'نشط']],
            ['code' => 'on_leave',   'name' => ['en' => 'On Leave',   'ar' => 'في إجازة']],
            ['code' => 'inactive',   'name' => ['en' => 'Inactive',   'ar' => 'غير نشط']],
            ['code' => 'terminated', 'name' => ['en' => 'Terminated', 'ar' => 'منتهي الخدمة']],
        ];

        foreach ($staffStatuses as $i => $row) {
            Status::firstOrCreate(
                ['code' => $row['code']],
                array_merge($row, ['scope' => 'staff', 'sort_order' => $i * 10])
            );
        }

        $this->seedSimple(AssetCondition::class, [
            ['excellent', 'Excellent', 'ممتاز'],
            ['good',      'Good',      'جيد'],
            ['fair',      'Fair',      'متوسط'],
            ['poor',      'Poor',      'سيئ'],
            ['damaged',   'Damaged',   'تالف'],
            ['broken',    'Broken',    'معطل'],
        ]);

        $this->seedSimple(CriticalityLevel::class, [
            ['critical', 'Critical', 'حرج'],
            ['high',     'High',     'عالي'],
            ['medium',   'Medium',   'متوسط'],
            ['low',      'Low',      'منخفض'],
        ]);

        $this->seedSimple(OwnershipType::class, [
            ['owned',  'Company Owned', 'ملك للشركة'],
            ['leased', 'Leased',        'مستأجر'],
            ['loaned', 'Loaned',        'معار'],
            ['personal', 'Personal',     'شخصي'],
        ]);

        $this->seedSimple(MaintenanceStatus::class, [
            ['scheduled',   'Scheduled',   'مجدول'],
            ['in_progress', 'In Progress', 'قيد التنفيذ'],
            ['completed',   'Completed',   'مكتمل'],
            ['cancelled',   'Cancelled',   'ملغي'],
            ['overdue',     'Overdue',     'متأخر'],
        ]);

        $this->seedSimple(MaintenanceType::class, [
            ['repair',         'Repair',         'إصلاح'],
            ['upgrade',        'Upgrade',        'تطوير'],
            ['inspection',     'Inspection',     'فحص'],
            ['preventive',     'Preventive',     'وقائي'],
            ['warranty_claim', 'Warranty Claim', 'مطالبة ضمان'],
        ]);

        $this->seedSimple(DisposalMethod::class, [
            ['sold',     'Sold',     'تم البيع'],
            ['donated',  'Donated',  'تم التبرع'],
            ['recycled', 'Recycled', 'تم التدوير'],
            ['scrapped', 'Scrapped', 'تم الخردة'],
            ['returned_to_vendor', 'Returned to Vendor', 'تم الإرجاع للمورد'],
        ]);

        $this->seedSimple(DisposalReason::class, [
            ['end_of_life',  'End of Life',     'انتهاء العمر الافتراضي'],
            ['obsolete',     'Obsolete',        'متقادم'],
            ['unrepairable', 'Unrepairable',    'غير قابل للإصلاح'],
            ['lost_stolen',  'Lost / Stolen',   'مفقود أو مسروق'],
            ['surplus',      'Surplus',         'فائض عن الحاجة'],
        ]);

        $this->seedSimple(WarrantyStatus::class, [
            ['under_warranty', 'Under Warranty', 'تحت الضمان'],
            ['expired',        'Expired',         'منتهي الضمان'],
            ['extended',       'Extended',        'ضمان ممدد'],
            ['none',           'No Warranty',     'بدون ضمان'],
        ]);

        $this->seedSimple(AssetAssignmentStatus::class, [
            ['available',       'Available',       'متاح'],
            ['assigned',        'Assigned',        'مكلف'],
            ['pending_return',  'Pending Return',  'قيد الإرجاع'],
            ['returned',        'Returned',        'تم الإرجاع'],
        ]);

        $this->seedSimple(AssetReturnReason::class, [
            ['end_of_assignment', 'End of Assignment', 'انتهاء التكليف'],
            ['transfer',          'Transfer',           'نقل'],
            ['upgrade',           'Upgrade',            'ترقية'],
            ['damaged',           'Damaged',            'تالف'],
            ['employee_left',     'Employee Left',      'انتهاء خدمة الموظف'],
        ]);

        $this->seedSimple(WarrantyProvider::class, [
            ['manufacturer',  'Manufacturer',    'الشركة المصنعة'],
            ['third_party',   'Third Party',     'طرف ثالث'],
            ['vendor',        'Vendor',          'المورد'],
            ['internal',      'Internal',        'داخلي'],
        ]);

        $this->seedSimple(EmploymentType::class, [
            ['full_time',  'Full Time',  'دوام كامل'],
            ['part_time',  'Part Time',  'دوام جزئي'],
            ['contractor', 'Contractor', 'متعاقد'],
            ['intern',     'Intern',     'متدرب'],
        ]);

        $this->seedSimple(Department::class, [
            ['admin',      'Administration', 'الإدارة'],
            ['it',         'IT',             'تقنية المعلومات'],
            ['hr',         'Human Resources', 'الموارد البشرية'],
            ['finance',    'Finance',        'المالية'],
            ['accounts_hr', 'Accounts / HR', 'الحسابات والموارد البشرية'],
            ['design',     'Design Team',    'فريق التصميم'],
            ['operations', 'Operations',     'العمليات'],
        ]);

        $this->seedSimple(JobTitle::class, [
            ['manager',     'Manager',          'مدير'],
            ['engineer',    'Engineer',         'مهندس'],
            ['analyst',     'Analyst',          'محلل'],
            ['designer',    'Designer',         'مصمم'],
            ['accountant',  'Accountant',       'محاسب'],
            ['technician',  'Technician',       'فني'],
            ['executive',   'Executive',        'تنفيذي'],
        ]);

        $this->seedSimple(OfficeLocation::class, [
            ['hq',          'HQ',              'المقر الرئيسي'],
            ['hq_level_2',  'HQ — Level 2',    'المقر الرئيسي — الطابق الثاني'],
            ['hq_it_room',  'HQ — IT Room',    'المقر الرئيسي — غرفة تقنية المعلومات'],
            ['hq_warehouse', 'HQ — Warehouse', 'المقر الرئيسي — المستودع'],
            ['remote',      'Remote',          'عن بُعد'],
            ['storage',     'Storage',         'المخزن'],
        ]);

        $this->seedSimple(Manufacturer::class, [
            ['apple',    'Apple',   'آبل'],
            ['dell',     'Dell',    'ديل'],
            ['hp',       'HP',      'إتش بي'],
            ['lg',       'LG',      'إل جي'],
            ['samsung',  'Samsung', 'سامسونج'],
            ['lenovo',   'Lenovo',  'لينوفو'],
        ]);

        $this->seedSimple(Supplier::class, [
            ['internal_store', 'Internal Store', 'المخزن الداخلي'],
            ['vendor_a',       'Vendor A',       'المورد أ'],
            ['vendor_b',       'Vendor B',       'المورد ب'],
        ]);
    }

    protected function seedSimple(string $modelClass, array $rows): void
    {
        foreach ($rows as $i => [$code, $en, $ar]) {
            $modelClass::firstOrCreate(
                ['code' => $code],
                [
                    'name' => ['en' => $en, 'ar' => $ar],
                    'sort_order' => $i * 10,
                    'is_active' => true,
                ]
            );
        }
    }
}
