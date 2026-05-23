<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\Lookups\AssetModel;
use App\Models\Lookups\Manufacturer;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\Status;
use App\Models\Lookups\Supplier;
use App\Services\Lookups\LookupResolver;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AssetsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure, WithChunkReading
{
    use SkipsFailures;

    protected LookupResolver $resolver;

    protected int $importedCount = 0;
    protected int $skippedCount = 0;
    protected array $seenSerials = [];

    public function __construct()
    {
        $this->resolver = new LookupResolver();

        $this->seenSerials = Asset::whereNotNull('serial_number')
            ->pluck('serial_number')
            ->map(fn ($s) => strtolower(trim($s)))
            ->flip()
            ->toArray();
    }

    public function model(array $row)
    {
        $row = $this->normaliseHeaders($row);

        $nameEn = trim((string) ($row['asset_name_english'] ?? $row['name_en'] ?? $row['name'] ?? ''));
        $nameAr = trim((string) ($row['asset_name_arabic'] ?? $row['name_ar'] ?? ''));

        if ($nameEn === '') {
            $this->skippedCount++;
            return null;
        }

        $serialNumber = trim((string) ($row['serial_number'] ?? '')) ?: null;
        if ($serialNumber) {
            $serialLower = strtolower($serialNumber);
            if (isset($this->seenSerials[$serialLower])) {
                $this->skippedCount++;
                return null;
            }
            $this->seenSerials[$serialLower] = true;
        }

        $categoryName = trim((string) ($row['asset_category'] ?? $row['category'] ?? ''));
        $subCategoryName = trim((string) ($row['asset_sub_category'] ?? $row['sub_category'] ?? ''));

        $categoryId = $categoryName ? $this->resolver->resolveCategory($categoryName, null) : null;
        $subCategoryId = $subCategoryName && $categoryId
            ? $this->resolver->resolveCategory($subCategoryName, $categoryId)
            : null;

        $manufacturerName = trim((string) ($row['manufacturer_brand'] ?? $row['manufacturer'] ?? ''));
        $manufacturerId = $manufacturerName
            ? $this->resolver->resolveOrCreate(Manufacturer::class, $manufacturerName)
            : null;

        $modelName = trim((string) ($row['model'] ?? ''));
        $modelId = $modelName
            ? $this->resolver->resolveOrCreate(
                AssetModel::class,
                $modelName,
                $manufacturerId ? ['manufacturer_id' => $manufacturerId] : []
            )
            : null;

        $supplierName = trim((string) (
            $row['purchased_from_store'] ?? $row['supplier'] ?? $row['store'] ?? ''
        ));
        $supplierId = $supplierName
            ? $this->resolver->resolveOrCreate(Supplier::class, $supplierName)
            : null;

        $statusId = $this->resolveStatusId($row['asset_status'] ?? $row['status'] ?? null);

        $locationName = trim((string) ($row['asset_location'] ?? $row['location'] ?? ''));
        $officeLocationId = $locationName
            ? $this->resolver->resolveOrCreate(OfficeLocation::class, $locationName)
            : null;

        $manufacturerYear = null;
        $rawYear = trim((string) ($row['manufacturer_year'] ?? ''));
        if ($rawYear !== '' && preg_match('/(\d{4})/', $rawYear, $m)) {
            $manufacturerYear = (int) $m[1];
        }

        $purchaseDate = $this->parseDate($row['purchase_date'] ?? null);
        $warrantyExpiry = $this->parseDate($row['warranty_expiry_date'] ?? $row['warranty_expiry'] ?? null);

        $purchaseCost = null;
        $rawCost = trim((string) ($row['purchase_cost'] ?? ''));
        if ($rawCost !== '') {
            $purchaseCost = (float) preg_replace('/[^0-9.]/', '', $rawCost);
        }

        $notesEn = trim((string) ($row['notes_english'] ?? $row['notes_en'] ?? ''));
        $notesAr = trim((string) ($row['notes_arabic'] ?? $row['notes_ar'] ?? ''));

        $availableAssignmentStatusId = Status::forAssignment()->where('code', 'available')->value('id');

        $this->importedCount++;

        return new Asset([
            'serial_number' => $serialNumber,
            'name' => array_filter(['en' => $nameEn, 'ar' => $nameAr ?: null]),
            'category_id' => $categoryId,
            'sub_category_id' => $subCategoryId,
            'manufacturer_id' => $manufacturerId,
            'model_id' => $modelId,
            'manufacturer_year' => $manufacturerYear,
            'supplier_id' => $supplierId,
            'status_id' => $statusId,
            'assignment_status_id' => $availableAssignmentStatusId,
            'purchase_date' => $purchaseDate,
            'purchase_cost' => $purchaseCost,
            'warranty_expiry' => $warrantyExpiry,
            'office_location_id' => $officeLocationId,
            'notes' => array_filter(['en' => $notesEn ?: null, 'ar' => $notesAr ?: null]),
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);
    }

    public function rules(): array
    {
        return [
            'asset_name_english' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer_year' => 'nullable',
            'purchase_date' => 'nullable',
            'purchase_cost' => 'nullable',
            'warranty_expiry_date' => 'nullable',
            'warranty_expiry' => 'nullable',
        ];
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    protected function normaliseHeaders(array $row): array
    {
        $clean = [];
        foreach ($row as $key => $value) {
            $clean[trim((string) $key)] = is_string($value) ? trim($value) : $value;
        }
        return $clean;
    }

    protected function resolveStatusId(?string $rawStatus): ?string
    {
        $raw = strtoupper(trim((string) $rawStatus));
        if ($raw === '') {
            return Status::forAssets()->where('code', 'purchased')->value('id');
        }

        $code = match ($raw) {
            'PURSHAED', 'PURCHASED', 'PURCHASE' => 'purchased',
            'AVAILABLE' => 'available',
            'RESERVED' => 'reserved',
            'ASSIGNED' => 'assigned',
            'IN_REPAIR', 'IN-REPAIR', 'REPAIR' => 'in_repair',
            'DAMAGED' => 'damaged',
            'RETIRED' => 'retired',
            'DISPOSED' => 'disposed',
            'LOST' => 'lost',
            default => 'purchased',
        };

        $id = Status::forAssets()->where('code', $code)->value('id');

        if (! $id) {
            $created = Status::create([
                'code' => $code,
                'name' => ['en' => ucfirst(str_replace('_', ' ', $code))],
                'scope' => 'asset',
                'is_active' => true,
            ]);
            $id = $created->id;
        }

        return $id;
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];
        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, trim((string) $value));
            if ($parsed !== false) {
                return $parsed->format('Y-m-d');
            }
        }

        try {
            return (new \DateTime($value))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
