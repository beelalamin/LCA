<?php

namespace App\Models;

use App\Models\Lookups\AssetCondition;
use App\Models\Lookups\AssetReturnReason;
use App\Models\Lookups\Department;
use App\Models\Lookups\MaintenanceType;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\Status;
use App\Models\Lookups\WarrantyProvider;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Transaction extends Model
{
    use HasUuids, HasTranslations;

    public const TYPE_REGISTERED = 'registered';
    public const TYPE_CHECK_OUT = 'check_out';
    public const TYPE_CHECK_IN = 'check_in';

    public static array $types = [
        self::TYPE_REGISTERED,
        self::TYPE_CHECK_OUT,
        self::TYPE_CHECK_IN,
    ];

    protected $guarded = [];

    public $translatable = ['notes'];

    protected $casts = [
        'checked_out_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class, 'office_location_id');
    }

    public function conditionOut(): BelongsTo
    {
        return $this->belongsTo(AssetCondition::class, 'condition_out_id');
    }

    public function conditionIn(): BelongsTo
    {
        return $this->belongsTo(AssetCondition::class, 'condition_in_id');
    }

    public function assignmentStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'assignment_status_id');
    }

    public function returnReason(): BelongsTo
    {
        return $this->belongsTo(AssetReturnReason::class, 'return_reason_id');
    }

    public function maintenanceStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'maintenance_status_id');
    }

    public function maintenanceType(): BelongsTo
    {
        return $this->belongsTo(MaintenanceType::class, 'maintenance_type_id');
    }

    public function warrantyProvider(): BelongsTo
    {
        return $this->belongsTo(WarrantyProvider::class, 'warranty_provider_id');
    }
}
