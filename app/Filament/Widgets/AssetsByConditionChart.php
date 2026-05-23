<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RtlAwareChart;
use App\Models\Lookups\AssetCondition;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetsByConditionChart extends ApexChartWidget
{
    use RtlAwareChart;

    protected static ?string $chartId = 'assetsByConditionChart';

    public function getHeading(): string
    {
        return __('Assets by Condition');
    }

    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;

    protected function getOptions(): array
    {
        $rows = AssetCondition::query()->orderBy('sort_order')->get();

        $counts = \DB::table('assets')
            ->select('condition_id', \DB::raw('count(*) as c'))
            ->whereNotNull('condition_id')
            ->groupBy('condition_id')
            ->pluck('c', 'condition_id');

        $colorMap = [
            'excellent' => '#10B981',
            'new' => '#10B981',
            'good' => '#22D3EE',
            'fair' => '#F59E0B',
            'poor' => '#F97316',
            'damaged' => '#EF4444',
            'broken' => '#EF4444',
        ];

        $labels = [];
        $series = [];
        $colors = [];
        foreach ($rows as $r) {
            $labels[] = $r->getTranslatedName();
            $series[] = (int) ($counts[$r->id] ?? 0);
            $colors[] = $colorMap[$r->code] ?? '#94A3B8';
        }

        return $this->rtlAware([
            'chart' => [
                'type' => 'bar',
                'height' => 320,
                'fontFamily' => 'inherit',
                'toolbar' => ['show' => false],
            ],
            'series' => [['name' => __('Assets'), 'data' => $series]],
            'xaxis' => [
                'categories' => $labels,
                'labels' => ['style' => ['fontSize' => '12px', 'colors' => '#64748B']],
            ],
            'yaxis' => [
                'labels' => ['style' => ['fontSize' => '12px', 'colors' => '#64748B']],
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 8,
                    'borderRadiusApplication' => 'end',
                    'columnWidth' => '55%',
                    'distributed' => true,
                ],
            ],
            'colors' => $colors,
            'dataLabels' => [
                'enabled' => true,
                'style' => ['fontSize' => '12px', 'fontWeight' => 600, 'colors' => ['#1E293B']],
                'offsetY' => -20,
            ],
            'legend' => ['show' => false],
            'grid' => [
                'borderColor' => '#E2E8F0',
                'strokeDashArray' => 4,
            ],
            'tooltip' => ['theme' => 'light'],
        ]);
    }
}
