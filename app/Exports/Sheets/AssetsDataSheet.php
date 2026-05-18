<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Comment;

class AssetsDataSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function title(): string
    {
        return 'Assets';
    }

    public function headings(): array
    {
        return [
            'name_en',
            'name_ar',
            'serial_number',
            'category',
            'manufacturer',
            'model',
            'status',
            'purchase_date',
            'purchase_cost',
            'warranty_expiry',
            'location',
            'notes_en',
            'notes_ar',
        ];
    }

    public function array(): array
    {
        // One example row to guide users
        return [
            [
                'Dell Latitude 5540',       // name_en
                'ديل لاتيتيود 5540',        // name_ar
                'SN-2026-001',              // serial_number
                '',                         // category (user fills from lookup)
                'Dell',                     // manufacturer
                'Latitude 5540',            // model
                'PURCHASED',                // status
                '2026-01-15',               // purchase_date
                '1250.00',                  // purchase_cost
                '2029-01-15',               // warranty_expiry
                'Head Office - Floor 3',    // location
                'Standard employee laptop', // notes_en
                'لابتوب موظف قياسي',        // notes_ar
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // name_en
            'B' => 25, // name_ar
            'C' => 20, // serial_number
            'D' => 25, // category
            'E' => 20, // manufacturer
            'F' => 20, // model
            'G' => 16, // status
            'H' => 16, // purchase_date
            'I' => 16, // purchase_cost
            'J' => 18, // warranty_expiry
            'K' => 25, // location
            'L' => 30, // notes_en
            'M' => 30, // notes_ar
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F4E79'],
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
            // Example row styling
            2 => [
                'font' => [
                    'italic' => true,
                    'color' => ['argb' => 'FF808080'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF2F2F2'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Freeze header row
                $sheet->freezePane('A2');

                // Set row height for header
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Mark required column (name_en) with a red asterisk via comment
                $sheet->getComment('A1')->getText()->createTextRun("REQUIRED\nThis field is mandatory for every row.");
                $sheet->getComment('A1')->setWidth('200px');
                $sheet->getComment('A1')->setHeight('50px');

                // Highlight required column header differently
                $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle('A1')->getFill()->getStartColor()->setARGB('FFD32F2F');

                // Add dropdown validation for Status column (G2:G1000) from the lookup sheet
                $statusValidation = $sheet->getCell('G2')->getDataValidation();
                $statusValidation->setType(DataValidation::TYPE_LIST);
                $statusValidation->setErrorStyle(DataValidation::STYLE_WARNING);
                $statusValidation->setAllowBlank(true);
                $statusValidation->setShowInputMessage(true);
                $statusValidation->setShowErrorMessage(true);
                $statusValidation->setErrorTitle('Invalid Status');
                $statusValidation->setError('Please select a valid status from the dropdown.');
                $statusValidation->setPromptTitle('Status');
                $statusValidation->setPrompt('Select from: PURCHASED, AVAILABLE, ASSIGNED, IN_REPAIR, RETIRED, DISPOSED');
                $statusValidation->setFormula1('"PURCHASED,AVAILABLE,ASSIGNED,IN_REPAIR,RETIRED,DISPOSED"');

                // Clone the validation to the rest of the column
                for ($row = 3; $row <= 1000; $row++) {
                    $sheet->getCell("G{$row}")->setDataValidation(clone $statusValidation);
                }

                // Add instructions comment on category column
                $sheet->getComment('D1')->getText()->createTextRun("Enter the category name exactly as shown in the 'Categories (Lookup)' sheet.");
                $sheet->getComment('D1')->setWidth('250px');
                $sheet->getComment('D1')->setHeight('40px');

                // Date format hint comments
                $sheet->getComment('H1')->getText()->createTextRun("Format: YYYY-MM-DD\nExample: 2026-01-15");
                $sheet->getComment('H1')->setWidth('180px');
                $sheet->getComment('H1')->setHeight('40px');

                $sheet->getComment('J1')->getText()->createTextRun("Format: YYYY-MM-DD\nExample: 2029-01-15");
                $sheet->getComment('J1')->setWidth('180px');
                $sheet->getComment('J1')->setHeight('40px');
            },
        ];
    }
}
