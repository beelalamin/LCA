<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AssetsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Assets by Status';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Asset::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Assets',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.5)', // AVAILABLE
                        'rgba(75, 192, 192, 0.5)', // PURCHASED
                        'rgba(255, 206, 86, 0.5)', // ASSIGNED
                        'rgba(255, 99, 132, 0.5)', // IN_REPAIR
                        'rgba(153, 102, 255, 0.5)' // RETIRED
                    ],
                ]
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
