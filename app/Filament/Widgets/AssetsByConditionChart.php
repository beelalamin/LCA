<?php

namespace App\Filament\Widgets;

use App\Models\Assignment;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetsByConditionChart extends ApexChartWidget
{
    protected static ?string $chartId = 'assetsByConditionChart';

    public function getHeading(): string
    {
        return __('Asset Condition Out (Last 10)');
    }
    protected static ?int $sort = 3;

    protected function getOptions(): array
    {
        $data = Assignment::select('condition_out', \DB::raw('count(*) as count'))
            ->groupBy('condition_out')
            ->get();

        return [
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'labels' => $data->pluck('condition_out')->map(fn($item) => __($item))->toArray(),
            'series' => $data->pluck('count')->map(fn($item) => (int) $item)->toArray(),
        ];
    }
}
