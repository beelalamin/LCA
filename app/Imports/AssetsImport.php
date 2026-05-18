<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\Category;
use App\Enums\AssetStatus;
use App\Services\AssetService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Illuminate\Support\Facades\Log;

class AssetsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure, WithChunkReading, WithEvents
{
    use SkipsFailures;

    protected array $categoryCache = [];
    protected int $importedCount = 0;
    protected int $skippedCount = 0;
    protected array $seenSerials = [];

    public function __construct()
    {
        // Pre-load all categories for fast lookup
        $categories = Category::all();
        foreach ($categories as $category) {
            // Index by both EN and AR names (lowercased) for flexible matching
            $enName = strtolower(trim($category->getTranslation('name', 'en')));
            $arName = strtolower(trim($category->getTranslation('name', 'ar')));

            if ($enName) {
                $this->categoryCache[$enName] = $category->id;
            }
            if ($arName) {
                $this->categoryCache[$arName] = $category->id;
            }
        }

        // Pre-load existing serial numbers to avoid duplicates
        $this->seenSerials = Asset::whereNotNull('serial_number')
            ->pluck('serial_number')
            ->map(fn ($s) => strtolower(trim($s)))
            ->flip()
            ->toArray();
    }

    public function model(array $row)
    {
        // Resolve category by name (case-insensitive)
        $categoryId = null;
        $categoryName = trim($row['category'] ?? '');
        if ($categoryName) {
            $categoryId = $this->categoryCache[strtolower($categoryName)] ?? null;
        }

        // Resolve status - default to PURCHASED if empty or invalid
        $statusValue = strtoupper(trim($row['status'] ?? 'PURCHASED'));
        $validStatuses = array_column(AssetStatus::cases(), 'value');
        if (!in_array($statusValue, $validStatuses)) {
            $statusValue = AssetStatus::PURCHASED->value;
        }

        // Build translatable name
        $nameEn = trim($row['name_en'] ?? '');
        $nameAr = trim($row['name_ar'] ?? '');

        if (empty($nameEn)) {
            $this->skippedCount++;
            return null; // name_en is required
        }

        // Check for duplicate serial_number
        $serialNumber = trim($row['serial_number'] ?? '') ?: null;
        if ($serialNumber) {
            $serialLower = strtolower($serialNumber);
            if (isset($this->seenSerials[$serialLower])) {
                $this->skippedCount++;
                return null; // Skip duplicate serial
            }
            $this->seenSerials[$serialLower] = true;
        }

        // Build translatable notes
        $notesEn = trim($row['notes_en'] ?? '');
        $notesAr = trim($row['notes_ar'] ?? '');

        // Parse purchase_cost - strip currency symbols & commas
        $purchaseCost = null;
        $rawCost = trim($row['purchase_cost'] ?? '');
        if ($rawCost !== '') {
            $purchaseCost = (float) preg_replace('/[^0-9.]/', '', $rawCost);
        }

        // Parse dates - handle multiple formats
        $purchaseDate = $this->parseDate($row['purchase_date'] ?? null);
        $warrantyExpiry = $this->parseDate($row['warranty_expiry'] ?? null);

        $this->importedCount++;

        // Use Asset::create() via the model() return so Eloquent events fire.
        // The AssetObserver will auto-generate asset_tag and set created_by.
        return new Asset([
            'serial_number'  => $serialNumber,
            'name'           => array_filter(['en' => $nameEn, 'ar' => $nameAr ?: null]),
            'category_id'    => $categoryId,
            'manufacturer'   => trim($row['manufacturer'] ?? '') ?: null,
            'model'          => trim($row['model'] ?? '') ?: null,
            'status'         => $statusValue,
            'purchase_date'  => $purchaseDate,
            'purchase_cost'  => $purchaseCost,
            'warranty_expiry' => $warrantyExpiry,
            'location'       => trim($row['location'] ?? '') ?: null,
            'notes'          => array_filter(['en' => $notesEn ?: null, 'ar' => $notesAr ?: null]),
            'created_by'     => auth()->id(),
        ]);
    }

    public function rules(): array
    {
        return [
            'name_en'       => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'category'      => 'nullable|string|max:255',
            'manufacturer'  => 'nullable|string|max:255',
            'model'         => 'nullable|string|max:255',
            'status'        => 'nullable|string|max:50',
            'purchase_date' => 'nullable',
            'purchase_cost' => 'nullable',
            'warranty_expiry' => 'nullable',
            'location'      => 'nullable|string|max:255',
            'name_ar'       => 'nullable|string|max:255',
            'notes_en'      => 'nullable|string|max:5000',
            'notes_ar'      => 'nullable|string|max:5000',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'name_en.required' => 'The Name (EN) column is required for each row.',
        ];
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // Only process the first sheet (Assets data sheet)
                // Skip lookup sheets if user accidentally leaves them
            },
        ];
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    /**
     * Parse a date value from Excel (could be numeric serial or string).
     */
    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // If it's a numeric Excel serial date
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Try standard date formats
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];
        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, trim($value));
            if ($parsed !== false) {
                return $parsed->format('Y-m-d');
            }
        }

        // Last resort - let PHP try to figure it out
        try {
            return (new \DateTime($value))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
