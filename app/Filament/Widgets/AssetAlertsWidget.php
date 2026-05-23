<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Lookups\Status;
use App\Models\Transaction;
use Filament\Widgets\Widget;

class AssetAlertsWidget extends Widget
{
    protected static string $view = 'filament.widgets.asset-alerts';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $today = now()->toDateString();
        $in30 = now()->addDays(30)->toDateString();
        $yearAgo = now()->subYear()->toDateString();
        $ninetyDaysAgo = now()->subDays(90)->toDateString();

        $warrantyExpiring = Asset::whereBetween('warranty_expiry', [$today, $in30])->count();

        $expiredId = Status::forWarranty()->where('code', 'expired')->value('id');
        $outOfWarranty = Asset::query()
            ->where(function ($q) use ($expiredId, $today) {
                $q->where('warranty_status_id', $expiredId)
                    ->orWhere('warranty_expiry', '<', $today);
            })->count();

        $maintenanceDue = Asset::whereBetween('next_maintenance_date', [$today, $in30])->count();
        $maintenanceOverdue = Asset::whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '<', $today)->count();

        $longOverdueReturns = Transaction::where('type', Transaction::TYPE_CHECK_OUT)
            ->whereNull('checked_in_at')
            ->where('checked_out_at', '<', $yearAgo)
            ->count();

        $availableStatusId = Status::forAssignment()->where('code', 'available')->value('id');
        $idleAssets = Asset::where('assignment_status_id', $availableStatusId)
            ->whereDoesntHave('transactions', fn ($q) => $q
                ->where('type', Transaction::TYPE_CHECK_OUT)
                ->where('checked_out_at', '>=', $ninetyDaysAgo))
            ->count();

        $criticalPoor = Asset::query()
            ->whereHas('criticality', fn ($q) => $q->whereIn('code', ['high', 'critical']))
            ->whereHas('condition', fn ($q) => $q->whereIn('code', ['poor', 'fair']))
            ->count();

        $alerts = [
            [
                'title' => __('Warranty Expiring'),
                'subtitle' => __('Next 30 days'),
                'count' => $warrantyExpiring,
                'icon' => 'heroicon-o-shield-exclamation',
                'severity' => 'warning',
            ],
            [
                'title' => __('Out of Warranty'),
                'subtitle' => __('Warranty has lapsed'),
                'count' => $outOfWarranty,
                'icon' => 'heroicon-o-shield-check',
                'severity' => 'danger',
            ],
            [
                'title' => __('Maintenance Due'),
                'subtitle' => __('Next 30 days'),
                'count' => $maintenanceDue,
                'icon' => 'heroicon-o-wrench-screwdriver',
                'severity' => 'warning',
            ],
            [
                'title' => __('Maintenance Overdue'),
                'subtitle' => __('Past scheduled date'),
                'count' => $maintenanceOverdue,
                'icon' => 'heroicon-o-exclamation-triangle',
                'severity' => 'danger',
            ],
            [
                'title' => __('Long-overdue Returns'),
                'subtitle' => __('Checked out > 1 year'),
                'count' => $longOverdueReturns,
                'icon' => 'heroicon-o-arrow-uturn-left',
                'severity' => 'danger',
            ],
            [
                'title' => __('Idle Assets'),
                'subtitle' => __('Available, unused 90d+'),
                'count' => $idleAssets,
                'icon' => 'heroicon-o-moon',
                'severity' => 'neutral',
            ],
            [
                'title' => __('Critical in Poor Condition'),
                'subtitle' => __('High criticality, fair/poor'),
                'count' => $criticalPoor,
                'icon' => 'heroicon-o-fire',
                'severity' => 'danger',
            ],
        ];

        $totalAlerts = array_sum(array_column($alerts, 'count'));
        $urgentAlerts = array_sum(array_map(
            fn ($a) => $a['severity'] === 'danger' ? $a['count'] : 0,
            $alerts
        ));

        return [
            'alerts' => $alerts,
            'totalAlerts' => $totalAlerts,
            'urgentAlerts' => $urgentAlerts,
        ];
    }
}
