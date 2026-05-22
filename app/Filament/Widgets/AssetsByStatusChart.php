<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Lookups\Status;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AssetsByStatusChart extends ChartWidget
{
    public function getHeading(): string
    {
        return __('Assets by Status');
    }

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $rows = Asset::query()
            ->select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->with('status')
            ->get();

        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            $status = $row->status;
            $labels[] = $status?->getTranslatedName() ?? __('Unknown');
            $values[] = (int) $row->count;
        }

        return [
            'datasets' => [
                [
                    'label' => __('Assets'),
                    'data' => $values,
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
