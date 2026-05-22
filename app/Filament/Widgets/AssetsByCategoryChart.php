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
    protected int|string|array $columnSpan = 1;

    protected function getOptions(): array
    {
        $rows = Category::withCount('assets')
            ->orderByDesc('assets_count')
            ->get();

        $top = $rows->take(6);
        $rest = $rows->slice(6);

        $labels = $top->pluck('name')->toArray();
        $series = $top->pluck('assets_count')->map(fn ($v) => (int) $v)->toArray();

        if ($rest->isNotEmpty()) {
            $labels[] = __('Other');
            $series[] = (int) $rest->sum('assets_count');
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 320,
                'fontFamily' => 'inherit',
                'toolbar' => ['show' => false],
            ],
            'series' => $series,
            'labels' => $labels,
            'colors' => ['#6366F1', '#06B6D4', '#10B981', '#F59E0B', '#EC4899', '#8B5CF6', '#94A3B8'],
            'stroke' => [
                'width' => 2,
                'colors' => ['#ffffff'],
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '68%',
                        'labels' => [
                            'show' => true,
                            'total' => [
                                'show' => true,
                                'label' => __('Total'),
                                'fontSize' => '14px',
                                'fontWeight' => 600,
                                'color' => '#64748B',
                            ],
                            'value' => [
                                'fontSize' => '24px',
                                'fontWeight' => 700,
                            ],
                        ],
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'style' => [
                    'fontSize' => '12px',
                    'fontWeight' => 600,
                ],
                'dropShadow' => ['enabled' => false],
            ],
            'legend' => [
                'position' => 'bottom',
                'fontSize' => '13px',
                'markers' => ['radius' => 12],
                'itemMargin' => ['horizontal' => 8, 'vertical' => 4],
            ],
            'tooltip' => [
                'theme' => 'light',
            ],
        ];
    }
}
