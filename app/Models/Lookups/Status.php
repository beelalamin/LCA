<?php

namespace App\Models\Lookups;

class Status extends BaseLookup
{
    protected $table = 'statuses';

    public const SCOPE_ASSET = 'asset';
    public const SCOPE_USER = 'user';
    public const SCOPE_WARRANTY = 'warranty';
    public const SCOPE_ASSIGNMENT = 'assignment';
    public const SCOPE_MAINTENANCE = 'maintenance';

    public static array $scopes = [
        self::SCOPE_ASSET,
        self::SCOPE_USER,
        self::SCOPE_WARRANTY,
        self::SCOPE_ASSIGNMENT,
        self::SCOPE_MAINTENANCE,
    ];

    public function scopeForAssets($query)
    {
        return $query->where('scope', self::SCOPE_ASSET);
    }

    public function scopeForUsers($query)
    {
        return $query->where('scope', self::SCOPE_USER);
    }

    public function scopeForWarranty($query)
    {
        return $query->where('scope', self::SCOPE_WARRANTY);
    }

    public function scopeForAssignment($query)
    {
        return $query->where('scope', self::SCOPE_ASSIGNMENT);
    }

    public function scopeForMaintenance($query)
    {
        return $query->where('scope', self::SCOPE_MAINTENANCE);
    }

    public function scopeOfScope($query, string $scope)
    {
        return $query->where('scope', $scope);
    }

    protected static function codeUniquenessScopeAttributes(BaseLookup $model): array
    {
        return ['scope' => $model->scope];
    }

    public function getColour(): string
    {
        if ($this->color) {
            return $this->color;
        }

        return match ($this->scope . ':' . $this->code) {
            // Asset
            'asset:purchased' => 'gray',
            'asset:reserved' => 'warning',
            'asset:available' => 'success',
            'asset:assigned' => 'info',
            'asset:in_repair' => 'warning',
            'asset:retired', 'asset:disposed', 'asset:damaged', 'asset:lost' => 'danger',
            // User
            'user:active' => 'success',
            'user:on_leave' => 'warning',
            'user:inactive', 'user:terminated' => 'danger',
            // Warranty
            'warranty:under_warranty', 'warranty:extended' => 'success',
            'warranty:expired' => 'danger',
            'warranty:none' => 'gray',
            // Assignment
            'assignment:available' => 'success',
            'assignment:assigned' => 'info',
            'assignment:pending_return' => 'warning',
            'assignment:returned' => 'gray',
            // Maintenance
            'maintenance:scheduled' => 'info',
            'maintenance:in_progress' => 'warning',
            'maintenance:completed' => 'success',
            'maintenance:cancelled' => 'gray',
            'maintenance:overdue' => 'danger',
            default => 'gray',
        };
    }
}
