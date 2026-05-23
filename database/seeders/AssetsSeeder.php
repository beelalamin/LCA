<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Lookups\AssetCondition;
use App\Models\Lookups\Manufacturer;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\Status;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssetsSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()
            ->where('email', 'admin@luxurycode.qa')
            ->value('id');

        if (! $adminId) {
            $this->command?->warn('Admin user not found — skipping asset seeding.');
            return;
        }

        $availableStatusId  = Status::forAssets()->where('code', 'available')->value('id');
        $availableAssignId  = Status::forAssignment()->where('code', 'available')->value('id');
        $goodConditionId    = AssetCondition::where('code', 'good')->value('id');
        $damagedConditionId = AssetCondition::where('code', 'damaged')->value('id');

        foreach ($this->assets() as $row) {
            if (Asset::where('serial_number', $row['serial'])->exists()) {
                continue;
            }

            $conditionId = $row['source_status'] === 'DAMAGED' ? $damagedConditionId : $goodConditionId;

            [$categoryId, $subCategoryId] = $this->resolveCategory($row['category'], $row['sub_category']);

            $nameField = ['en' => $row['name']];
            $description = $row['model'] !== '' ? ['en' => $row['model']] : null;
            $notes = $row['notes'] !== '' ? ['en' => $row['notes']] : null;

            $asset = Asset::create([
                'serial_number'        => $row['serial'],
                'name'                 => $nameField,
                'category_id'          => $categoryId,
                'sub_category_id'      => $subCategoryId,
                'manufacturer_id'      => $this->resolveManufacturer($row['manufacturer']),
                'manufacturer_year'    => $row['year'],
                'status_id'            => $availableStatusId,
                'office_location_id'   => $this->resolveLocation($row['location']),
                'condition_id'         => $conditionId,
                'description'          => $description,
                'notes'                => $notes,
                'assignment_status_id' => $availableAssignId,
                'is_active'            => true,
                'created_by'           => $adminId,
            ]);

            Transaction::create([
                'asset_id'             => $asset->id,
                'type'                 => Transaction::TYPE_REGISTERED,
                'user_id'              => $adminId,
                'assigned_by'          => $adminId,
                'assignment_status_id' => $availableAssignId,
                'condition_out_id'     => $conditionId,
            ]);
        }
    }

    protected function resolveManufacturer(string $name): ?string
    {
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

    protected function resolveLocation(string $name): ?string
    {
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
    protected function resolveCategory(string $catName, string $subName): array
    {
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

    /**
     * Asset inventory imported from Updated_Asset_Management.csv.
     * source_status is the original CSV value, used only to derive condition_id.
     *
     * @return array<int, array{
     *     serial: string, name: string, category: string, sub_category: string,
     *     manufacturer: string, model: string, year: ?int, source_status: string,
     *     location: string, notes: string
     * }>
     */
    protected function assets(): array
    {
        return [
            ['serial' => 'SN-2026-001', 'name' => 'Dell',                       'category' => 'Admin',             'sub_category' => 'Desktop',              'manufacturer' => 'Dell',                       'model' => 'Inspiron 24 5420 (All-in-one) — Generic Monitor (DELL AIO)', 'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Workspace 1', 'notes' => "In Angel's use"],
            ['serial' => 'SN-2026-002', 'name' => 'Dell',                       'category' => 'Accounts/HR',       'sub_category' => 'Desktop',              'manufacturer' => 'Dell',                       'model' => 'Inspiron 24 5420 (All-in-one) — DESKTOP-NKU7SVO',           'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Workspace 1', 'notes' => "In Abdalla's use"],
            ['serial' => 'SN-2026-003', 'name' => 'Lenovo Legion',              'category' => 'Laptop',            'sub_category' => 'Laptop',               'manufacturer' => 'Lenovo Legion',              'model' => 'Lenovo Legion',                                            'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Workspace 1', 'notes' => 'Shared asset used for meetings, presentations, and technical support.'],
            ['serial' => 'SN-2026-004', 'name' => 'BenQ',                       'category' => 'Design Team',       'sub_category' => 'Monitors',             'manufacturer' => 'BenQ',                       'model' => 'DESKTOP-8LPGUA0 (MS-7A71)',                                'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Shaikha's use"],
            ['serial' => 'SN-2026-005', 'name' => 'ASUS',                       'category' => 'Design Team',       'sub_category' => 'Monitors',             'manufacturer' => 'ASUS',                       'model' => 'Z790 UD AX',                                               'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Dana's use"],
            ['serial' => 'SN-2026-006', 'name' => 'ASUS (HDMI) + BenQ',         'category' => 'Design Team',       'sub_category' => '2 Monitors',           'manufacturer' => 'ASUS (HDMI) + BenQ',         'model' => 'Generic PnP Monitor — ZOWIE RL LCD',                       'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Nadeen's use"],
            ['serial' => 'SN-2026-007', 'name' => 'BenQ',                       'category' => 'Design Team',       'sub_category' => 'Monitors',             'manufacturer' => 'BenQ',                       'model' => 'DESKTOP-LL96Q6V',                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Afsal's use"],
            ['serial' => 'SN-2026-008', 'name' => 'Xiaomi',                     'category' => 'Design Team',       'sub_category' => 'Monitors',             'manufacturer' => 'Xiaomi',                     'model' => 'DESKTOP-K08E7EV — MEK Ultra',                              'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Anbu's use"],
            ['serial' => 'SN-2026-009', 'name' => 'BenQ + Dell',                'category' => 'Design Team',       'sub_category' => '2 Monitors',           'manufacturer' => 'BenQ + Dell',                'model' => 'BenQ GW2780 (NVIDIA High Definition Audio) — Generic PnP Monitor', 'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2', 'notes' => "In Montassar's use"],
            ['serial' => 'SN-2026-010', 'name' => 'ASUS',                       'category' => 'Design Team',       'sub_category' => 'Monitors',             'manufacturer' => 'ASUS',                       'model' => 'DESKTOP-SOMHVPM',                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Tixan's use"],
            ['serial' => 'SN-2026-011', 'name' => 'DELL',                       'category' => 'Admin',             'sub_category' => 'Mouse',                'manufacturer' => 'DELL',                       'model' => 'MS3121Wt',                                                 'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Workspace 1', 'notes' => "In Angel's use"],
            ['serial' => 'SN-2026-012', 'name' => 'DELL',                       'category' => 'Accounts/HR',       'sub_category' => 'Mouse',                'manufacturer' => 'DELL',                       'model' => 'MS3121Wt',                                                 'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Workspace 1', 'notes' => "In Abdalla's use"],
            ['serial' => 'SN-2026-013', 'name' => 'DELL',                       'category' => 'Admin',             'sub_category' => 'Keyboard',             'manufacturer' => 'DELL',                       'model' => 'Universal Receiver',                                       'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Workspace 1', 'notes' => "In Angel's use"],
            ['serial' => 'SN-2026-014', 'name' => 'DELL',                       'category' => 'Accounts/HR',       'sub_category' => 'Keyboard',             'manufacturer' => 'DELL',                       'model' => 'Universal Receiver',                                       'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Workspace 1', 'notes' => "In Abdalla's use"],
            ['serial' => 'SN-2026-015', 'name' => 'HP',                         'category' => 'Design Team',       'sub_category' => 'Keyboard',             'manufacturer' => 'HP',                         'model' => 'HSA-AD04K',                                                'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Shaikha's use"],
            ['serial' => 'SN-2026-016', 'name' => 'HP',                         'category' => 'Design Team',       'sub_category' => 'Mouse',                'manufacturer' => 'HP',                         'model' => '7CH4425587',                                               'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Shaikha's use"],
            ['serial' => 'SN-2026-017', 'name' => 'Intel',                      'category' => 'Design Team',       'sub_category' => 'CPU',                  'manufacturer' => 'Intel',                      'model' => 'KK3-0009',                                                 'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Shaikha's use"],
            ['serial' => 'SN-2026-018', 'name' => 'Lenovo Legion',              'category' => 'Design Team',       'sub_category' => 'Keyboard',             'manufacturer' => 'Lenovo Legion',              'model' => 'PR1101U',                                                  'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Dana's use"],
            ['serial' => 'SN-2026-019', 'name' => 'HP',                         'category' => 'Design Team',       'sub_category' => 'Mouse',                'manufacturer' => 'HP',                         'model' => '(MODGUO)',                                                 'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Dana's use"],
            ['serial' => 'SN-2026-020', 'name' => 'LIAH L1',                    'category' => 'Design Team',       'sub_category' => 'CPU',                  'manufacturer' => 'LIAH L1',                    'model' => '',                                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Dana's use"],
            ['serial' => 'SN-2026-021', 'name' => 'Lenovo',                     'category' => 'Design Team',       'sub_category' => 'Keyboard',             'manufacturer' => 'Lenovo',                     'model' => 'SK-8823',                                                  'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Nadeen's use"],
            ['serial' => 'SN-2026-022', 'name' => 'Wireless Mouse (Kingston)',  'category' => 'Design Team',       'sub_category' => 'Mouse',                'manufacturer' => 'Wireless Mouse (Kingston)',  'model' => 'GV3M61291-M',                                              'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Nadeen's use"],
            ['serial' => 'SN-2026-023', 'name' => 'ZOTAC Gaming',               'category' => 'Design Team',       'sub_category' => 'CPU',                  'manufacturer' => 'ZOTAC Gaming',               'model' => '',                                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Nadeen's use"],
            ['serial' => 'SN-2026-024', 'name' => 'CyberPowerPC',               'category' => 'Design Team',       'sub_category' => 'Keyboard',             'manufacturer' => 'CyberPowerPC',               'model' => 'Gaming Keyboard',                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Afsal's use"],
            ['serial' => 'SN-2026-025', 'name' => 'Logitech',                   'category' => 'Design Team',       'sub_category' => 'Mouse',                'manufacturer' => 'Logitech',                   'model' => 'M90 (M-00026)',                                            'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Afsal's use"],
            ['serial' => 'SN-2026-026', 'name' => 'ZOTAC Gaming',               'category' => 'Design Team',       'sub_category' => 'CPU',                  'manufacturer' => 'ZOTAC Gaming',               'model' => '',                                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Afsal's use"],
            ['serial' => 'SN-2026-027', 'name' => 'Philips',                    'category' => 'Design Team',       'sub_category' => 'Mouse',                'manufacturer' => 'Philips',                    'model' => 'SPK7404',                                                  'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Anbu's use"],
            ['serial' => 'SN-2026-028', 'name' => 'ZOTAC Gaming',               'category' => 'Design Team',       'sub_category' => 'CPU',                  'manufacturer' => 'ZOTAC Gaming',               'model' => '',                                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Anbu's use"],
            ['serial' => 'SN-2026-029', 'name' => 'HP',                         'category' => 'Design Team',       'sub_category' => 'Keyboard',             'manufacturer' => 'HP',                         'model' => 'HAS-A013K',                                                'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Anbu's use"],
            ['serial' => 'SN-2026-030', 'name' => 'POSC',                       'category' => 'Design Team',       'sub_category' => 'Keyboard',             'manufacturer' => 'POSC',                       'model' => '',                                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Montassar's use"],
            ['serial' => 'SN-2026-031', 'name' => 'LAN HEAR',                   'category' => 'Design Team',       'sub_category' => 'Mouse',                'manufacturer' => 'LAN HEAR',                   'model' => 'Gaming Mouse',                                             'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Montassar's use"],
            ['serial' => 'SN-2026-032', 'name' => 'ZOTAC Gaming',               'category' => 'Design Team',       'sub_category' => 'CPU',                  'manufacturer' => 'ZOTAC Gaming',               'model' => '',                                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Montassar's use"],
            ['serial' => 'SN-2026-033', 'name' => 'Logitech',                   'category' => 'Design Team',       'sub_category' => 'Keyboard',             'manufacturer' => 'Logitech',                   'model' => '',                                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Tixan's use"],
            ['serial' => 'SN-2026-034', 'name' => 'Logitech M90',               'category' => 'Design Team',       'sub_category' => 'Mouse',                'manufacturer' => 'Logitech M90',               'model' => 'M-U0026',                                                  'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Tixan's use"],
            ['serial' => 'SN-2026-035', 'name' => 'GeForce RTX',                'category' => 'Design Team',       'sub_category' => 'CPU',                  'manufacturer' => 'GeForce RTX',                'model' => '',                                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 2',    'notes' => "In Tixan's use"],
            ['serial' => 'SN-2026-036', 'name' => 'Brother',                    'category' => 'Accounts/HR',       'sub_category' => 'Printer',              'manufacturer' => 'Brother',                    'model' => 'HL-L3270CDW',                                              'year' => null, 'source_status' => 'RESERVED', 'location' => 'Head Office - Office 1',    'notes' => "In Abdalla's use"],
            ['serial' => 'SN-2026-037', 'name' => 'Toshiba',                    'category' => 'Admin/Design Team', 'sub_category' => 'Printer',              'manufacturer' => 'Toshiba',                    'model' => 'e-studio2020 AC',                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Server Room',               'notes' => 'Common Use'],
            ['serial' => 'SN-2026-038', 'name' => 'Kensington',                 'category' => 'Storage',           'sub_category' => 'Keyboard (Wireless)',  'manufacturer' => 'Kensington',                 'model' => 'M01369-K',                                                 'year' => null, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-039', 'name' => 'Kensington',                 'category' => 'Storage',           'sub_category' => 'Keyboard (Wireless)',  'manufacturer' => 'Kensington',                 'model' => 'M01369-K',                                                 'year' => null, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-040', 'name' => 'Microsoft Redmond',          'category' => 'Storage',           'sub_category' => 'Keyboard (Wireless 2000)', 'manufacturer' => 'Microsoft Redmond',      'model' => '1477',                                                     'year' => null, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-041', 'name' => 'Logitech MK710',             'category' => 'Storage',           'sub_category' => 'Keyboard (Wireless)',  'manufacturer' => 'Logitech MK710',             'model' => 'Y-R0059',                                                  'year' => null, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-042', 'name' => 'Lenovo Ideapad 310',         'category' => 'Storage',           'sub_category' => 'Laptop',               'manufacturer' => 'Lenovo Ideapad 310',         'model' => '80TU',                                                     'year' => 2014, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-043', 'name' => 'Kensington',                 'category' => 'Storage',           'sub_category' => 'Wireless Mouse',       'manufacturer' => 'Kensington',                 'model' => 'M01291-M',                                                 'year' => null, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-044', 'name' => 'Logitech M750',              'category' => 'Storage',           'sub_category' => 'Wireless Mouse',       'manufacturer' => 'Logitech M750',              'model' => 'M-R0073',                                                  'year' => null, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-045', 'name' => 'LINKSYS N600',               'category' => 'Storage',           'sub_category' => 'Wi-Fi Router',         'manufacturer' => 'LINKSYS N600',               'model' => 'E2500',                                                    'year' => null, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-046', 'name' => 'DELL KB212-B',               'category' => 'Storage',           'sub_category' => 'Keyboard',             'manufacturer' => 'DELL KB212-B',               'model' => 'SK-8120',                                                  'year' => null, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-047', 'name' => 'Ronie 4118/4',               'category' => 'Storage',           'sub_category' => 'LED COB 10 W',         'manufacturer' => 'Ronie 4118/4',               'model' => 'R411810N — 3000 K',                                        'year' => null, 'source_status' => 'PURCHASED','location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-048', 'name' => 'G.SKILL',                    'category' => 'Storage',           'sub_category' => 'Memory Card',          'manufacturer' => 'G.SKILL',                    'model' => 'Ripjaws V DDR4',                                           'year' => null, 'source_status' => 'PURCHASED','location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-049', 'name' => 'G.SKILL',                    'category' => 'Storage',           'sub_category' => 'Memory Card',          'manufacturer' => 'G.SKILL',                    'model' => 'Ripjaws V DDR4',                                           'year' => null, 'source_status' => 'PURCHASED','location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-050', 'name' => 'GIGABYTE',                   'category' => 'Storage',           'sub_category' => 'Memory Card',          'manufacturer' => 'GIGABYTE',                   'model' => '12QM-AM51700-10CAR',                                       'year' => null, 'source_status' => 'PURCHASED','location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-051', 'name' => 'GAMEMAX',                    'category' => 'Storage',           'sub_category' => 'Modular Output Cable', 'manufacturer' => 'GAMEMAX',                    'model' => 'RGB-1300-PRO',                                             'year' => null, 'source_status' => 'PURCHASED','location' => 'Head Office Store',         'notes' => '6 cables in a pack'],
            ['serial' => 'SN-2026-052', 'name' => 'DELL',                       'category' => 'Storage',           'sub_category' => 'CPU',                  'manufacturer' => 'DELL',                       'model' => 'OPTIPLEX 7020',                                            'year' => null, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-053', 'name' => 'CyberPowerPC',               'category' => 'Storage',           'sub_category' => 'CPU',                  'manufacturer' => 'CyberPowerPC',               'model' => 'C Series',                                                 'year' => null, 'source_status' => 'DAMAGED',  'location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-054', 'name' => 'Galaxy M31',                 'category' => 'Mobile',            'sub_category' => 'Mobile Phone',         'manufacturer' => 'Samsung',                    'model' => 'SM-M315F/DSN',                                             'year' => null, 'source_status' => 'RESERVED', 'location' => 'Office and carries along',  'notes' => "In Angel's use"],
            ['serial' => 'SN-2026-055', 'name' => 'SIM Card',                   'category' => 'Mobile',            'sub_category' => 'Office SIM',           'manufacturer' => 'Ooredoo',                    'model' => '50167222 - Phone No.',                                     'year' => null, 'source_status' => 'RESERVED', 'location' => 'Office and carries along',  'notes' => "In Angel's use"],
            ['serial' => 'SN-2026-056', 'name' => 'SIM Card',                   'category' => 'Mobile',            'sub_category' => 'Office SIM',           'manufacturer' => 'Ooredoo',                    'model' => '33491444 - Phone No.',                                     'year' => null, 'source_status' => 'RESERVED', 'location' => 'Office and carries along',  'notes' => "In Abdalla's use"],
            ['serial' => 'SN-2026-057', 'name' => 'SIM Card',                   'category' => 'Mobile',            'sub_category' => 'Office SIM',           'manufacturer' => 'Ooredoo',                    'model' => '50267444 - Phone No.',                                     'year' => null, 'source_status' => 'RESERVED', 'location' => 'Office and carries along',  'notes' => "In Montassar's use"],
            ['serial' => 'SN-2026-058', 'name' => 'SIM Card',                   'category' => 'Mobile',            'sub_category' => 'Office SIM',           'manufacturer' => 'Ooredoo',                    'model' => '50268444 - Phone No.',                                     'year' => null, 'source_status' => 'RESERVED', 'location' => 'Office and carries along',  'notes' => "In Nadeen's use"],
            ['serial' => 'SN-2026-059', 'name' => 'SIM Card',                   'category' => 'Mobile',            'sub_category' => 'Office SIM',           'manufacturer' => 'Ooredoo',                    'model' => '55620689 - Phone No.',                                     'year' => null, 'source_status' => 'RESERVED', 'location' => 'Office and carries along',  'notes' => "In Dana's use"],
            ['serial' => 'SN-2026-060', 'name' => 'SIM Card',                   'category' => 'Mobile',            'sub_category' => 'Office SIM',           'manufacturer' => 'Ooredoo',                    'model' => '66194561 - Phone No.',                                     'year' => null, 'source_status' => 'RESERVED', 'location' => 'Office and carries along',  'notes' => "In Thabet's use"],
            ['serial' => 'SN-2026-061', 'name' => 'Belkin',                     'category' => 'Accessories',       'sub_category' => 'Multiport Adapter',    'manufacturer' => 'Belkin',                     'model' => 'AVC006',                                                   'year' => null, 'source_status' => 'AVAILABLE','location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-062', 'name' => 'Microsoft Surface Go 2',     'category' => 'Tablets',           'sub_category' => 'Surface Go 2',         'manufacturer' => 'Microsoft',                  'model' => '',                                                          'year' => null, 'source_status' => 'AVAILABLE','location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-063', 'name' => 'Razer Blade',                'category' => 'Laptop',            'sub_category' => 'Laptop',               'manufacturer' => 'Razer Blade',                'model' => '',                                                          'year' => null, 'source_status' => 'AVAILABLE','location' => 'Head Office Store',         'notes' => ''],
            ['serial' => 'SN-2026-064', 'name' => 'iPad',                       'category' => 'Tablets',           'sub_category' => 'iPad',                 'manufacturer' => 'Apple',                      'model' => '',                                                          'year' => null, 'source_status' => 'RESERVED', 'location' => 'Office and carries along',  'notes' => "In Dana's use"],
            ['serial' => 'SN-2026-065', 'name' => 'Oppo A3 Pro',                'category' => 'Mobile',            'sub_category' => 'Mobile Phone',         'manufacturer' => 'Oppo',                       'model' => 'A3 Pro 5G',                                                'year' => null, 'source_status' => 'RESERVED', 'location' => 'Office and carries along',  'notes' => "In Montassar's use"],
        ];
    }
}
