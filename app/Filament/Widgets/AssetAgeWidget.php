<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RtlAwareChart;
use App\Models\Asset;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetAgeWidget extends ApexChartWidget
{
    use RtlAwareChart;

    protected static ?string $chartId = 'assetAgeChart';

    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;

    public function getHeading(): string
    {
        return __('Asset Age & Lifecycle');
    }

    protected function getOptions(): array
    {
        $today = now();
        $oneYearAgo = $today->copy()->subYear()->toDateString();
        $threeYearsAgo = $today->copy()->subYears(3)->toDateString();
        $sixYearsAgo = $today->copy()->subYears(6)->toDateString();
        $cutoff2020 = '2020-01-01';

        $base = Asset::query()->whereNull('disposal_date');

        $new = (clone $base)->where('purchase_date', '>=', $oneYearAgo)->count();
        $recent = (clone $base)->whereBetween('purchase_date', [$threeYearsAgo, $oneYearAgo])->count();
        $aging = (clone $base)->whereBetween('purchase_date', [$sixYearsAgo, $threeYearsAgo])->count();
        $veryOld = (clone $base)->where('purchase_date', '<', $cutoff2020)->count();
        $disposed = Asset::whereNotNull('disposal_date')->count();

        $labels = [
            __('New (< 1y)'),
            __('Recent (1-3y)'),
            __('Aging (3-6y)'),
            __('Very Old (pre-2020)'),
            __('Disposed'),
        ];
        $series = [$new, $recent, $aging, $veryOld, $disposed];

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
                    'horizontal' => true,
                    'borderRadius' => 6,
                    'borderRadiusApplication' => 'end',
                    'barHeight' => '70%',
                    'distributed' => true,
                ],
            ],
            'colors' => ['#10B981', '#22D3EE', '#F59E0B', '#F97316', '#94A3B8'],
            'dataLabels' => [
                'enabled' => true,
                'style' => ['fontSize' => '11px', 'fontWeight' => 600, 'colors' => ['#fff']],
                'offsetX' => 24,
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
