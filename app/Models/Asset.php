<?php

namespace App\Models;

use App\Models\Lookups\AssetAssignmentStatus;
use App\Models\Lookups\AssetCondition;
use App\Models\Lookups\AssetModel;
use App\Models\Lookups\AssetReturnReason;
use App\Models\Lookups\CriticalityLevel;
use App\Models\Lookups\Department;
use App\Models\Lookups\DisposalMethod;
use App\Models\Lookups\DisposalReason;
use App\Models\Lookups\MaintenanceStatus;
use App\Models\Lookups\MaintenanceType;
use App\Models\Lookups\Manufacturer;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\OwnershipType;
use App\Models\Lookups\Status;
use App\Models\Lookups\Supplier;
use App\Models\Lookups\WarrantyProvider;
use App\Models\Lookups\WarrantyStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Translatable\HasTranslations;

class Asset extends Model
{
    use HasUuids, HasTranslations;

    protected $guarded = [];

    public $translatable = ['name', 'notes', 'description'];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'assigned_date' => 'date',
        'return_date' => 'date',
        'disposal_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function assetModel(): BelongsTo
    {
        return $this->belongsTo(AssetModel::class, 'model_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class, 'office_location_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(AssetCondition::class, 'condition_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function warrantyStatus(): BelongsTo
    {
        return $this->belongsTo(WarrantyStatus::class, 'warranty_status_id');
    }

    public function warrantyProvider(): BelongsTo
    {
        return $this->belongsTo(WarrantyProvider::class, 'warranty_provider_id');
    }

    public function criticality(): BelongsTo
    {
        return $this->belongsTo(CriticalityLevel::class, 'criticality_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignmentStatus(): BelongsTo
    {
        return $this->belongsTo(AssetAssignmentStatus::class, 'assignment_status_id');
    }

    public function returnReason(): BelongsTo
    {
        return $this->belongsTo(AssetReturnReason::class, 'return_reason_id');
    }

    public function ownershipType(): BelongsTo
    {
        return $this->belongsTo(OwnershipType::class, 'ownership_type_id');
    }

    public function maintenanceStatus(): BelongsTo
    {
        return $this->belongsTo(MaintenanceStatus::class, 'maintenance_status_id');
    }

    public function maintenanceType(): BelongsTo
    {
        return $this->belongsTo(MaintenanceType::class, 'maintenance_type_id');
    }

    public function disposalMethod(): BelongsTo
    {
        return $this->belongsTo(DisposalMethod::class, 'disposal_method_id');
    }

    public function disposalReason(): BelongsTo
    {
        return $this->belongsTo(DisposalReason::class, 'disposal_reason_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'asset_id');
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class, 'asset_id')->where('is_active', true);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class, 'asset_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'asset_id');
    }

    public function getStatusCode(): ?string
    {
        return $this->status?->code;
    }
}
