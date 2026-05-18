<?php

namespace App\Exports\Sheets;

use App\Enums\AssetStatus;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StatusLookupSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Status Values (Lookup)';
    }

    public function headings(): array
    {
        return [
            'Status Value',
            'Description',
        ];
    }

    public function array(): array
    {
        return [
            ['PURCHASED',  'Asset has been purchased but not yet deployed'],
            ['AVAILABLE',  'Asset is available and ready for assignment'],
            ['ASSIGNED',   'Asset is currently assigned to an employee'],
            ['IN_REPAIR',  'Asset is undergoing maintenance or repair'],
            ['RETIRED',    'Asset has been retired from active use'],
            ['DISPOSED',   'Asset has been disposed of permanently'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 50,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE65100'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ],
        ];
    }
}
