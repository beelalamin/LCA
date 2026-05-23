<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ChartsTabsWidget extends Widget
{
    protected static string $view = 'filament.widgets.charts-tabs';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public string $activeTab = 'inventory';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getTabs(): array
    {
        return [
            'inventory' => [
                'label' => __('Inventory'),
                'icon' => 'heroicon-o-archive-box',
                'charts' => [
                    AssetsByCategoryChart::class,
                ],
            ],
            'people' => [
                'label' => __('People'),
                'icon' => 'heroicon-o-user-group',
                'charts' => [
                    AssetsByDepartmentChart::class,
                ],
            ],
            'locations' => [
                'label' => __('Locations'),
                'icon' => 'heroicon-o-map-pin',
                'charts' => [
                    AssetsByOfficeLocationChart::class,
                ],
            ],
            'health' => [
                'label' => __('Health'),
                'icon' => 'heroicon-o-heart',
                'charts' => [
                    AssetsByConditionChart::class,
                    AssetsByCriticalityChart::class,
                ],
            ],
            'lifecycle' => [
                'label' => __('Lifecycle'),
                'icon' => 'heroicon-o-clock',
                'charts' => [
                    AssetAgeWidget::class,
                ],
            ],
        ];
    }
}
