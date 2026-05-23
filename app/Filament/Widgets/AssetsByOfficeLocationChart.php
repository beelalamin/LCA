<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RtlAwareChart;
use App\Models\Lookups\OfficeLocation;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetsByOfficeLocationChart extends ApexChartWidget
{
    use RtlAwareChart;

    protected static ?string $chartId = 'assetsByOfficeLocationChart';

    public function getHeading(): string
    {
        return __('Assets by Office Location');
    }

    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 1;

    protected function getOptions(): array
    {
        $rows = OfficeLocation::query()->orderBy('sort_order')->get();

        $counts = \DB::table('assets')
            ->select('office_location_id', \DB::raw('count(*) as c'))
            ->whereNotNull('office_location_id')
            ->groupBy('office_location_id')
            ->pluck('c', 'office_location_id');

        $labels = [];
        $series = [];
        foreach ($rows as $r) {
            $labels[] = $r->getTranslatedName();
            $series[] = (int) ($counts[$r->id] ?? 0);
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
                    'horizontal' => true,
                    'borderRadius' => 6,
                    'borderRadiusApplication' => 'end',
                    'barHeight' => '70%',
                    'distributed' => true,
                ],
            ],
            'colors' => ['#0EA5E9', '#22D3EE', '#34D399', '#FBBF24', '#F472B6', '#A78BFA', '#FB923C', '#60A5FA'],
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
