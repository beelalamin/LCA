<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RtlAwareChart;
use App\Models\Lookups\Department;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetsByDepartmentChart extends ApexChartWidget
{
    use RtlAwareChart;

    protected static ?string $chartId = 'assetsByDepartmentChart';

    public function getHeading(): string
    {
        return __('Assets by Department');
    }

    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 1;

    protected function getOptions(): array
    {
        $rows = Department::query()->orderBy('sort_order')->get();

        $counts = \DB::table('assets')
            ->select('department_id', \DB::raw('count(*) as c'))
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->pluck('c', 'department_id');

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
            'colors' => ['#6366F1', '#06B6D4', '#10B981', '#F59E0B', '#EC4899', '#8B5CF6', '#14B8A6', '#F97316', '#3B82F6', '#A855F7'],
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
