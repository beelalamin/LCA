<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetsByCategoryChart extends ApexChartWidget
{
    protected static ?string $chartId = 'assetsByCategoryChart';
    protected static ?string $heading = 'Assets by Category';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    protected function getOptions(): array
    {
        $data = Category::withCount('assets')->get();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Assets',
                    'data' => $data->pluck('assets_count')->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $data->pluck('name')->toArray(),
            ],
        ];
    }
}
