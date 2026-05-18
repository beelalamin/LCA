<?php

namespace App\Exports;

use App\Models\Category;
use App\Enums\AssetStatus;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AssetsTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Assets'                 => new Sheets\AssetsDataSheet(),
            'Categories (Lookup)'    => new Sheets\CategoriesLookupSheet(),
            'Status Values (Lookup)' => new Sheets\StatusLookupSheet(),
        ];
    }
}
