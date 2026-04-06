<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetsByCategoryChart extends ApexChartWidget
{
    protected static ?string $chartId = 'assetsByCategoryChart';
    
    public function getHeading(): string
    {
        return __('Assets by Category');
    }

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
                    'name' => __('Assets'),
                    'data' => $data->pluck('assets_count')->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $data->pluck('name')->toArray(),
            ],
        ];
    }
}
