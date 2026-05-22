<?php

namespace App\Models;

use App\Models\Lookups\Department;
use App\Models\Lookups\EmploymentType;
use App\Models\Lookups\JobTitle;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\Status;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'joining_date' => 'date',
        'leaving_date' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }

    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class, 'employment_type_id');
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class, 'office_location_id');
    }

    public function lineManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'line_manager_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'employee_id');
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'employee_id')->where('is_active', true);
    }

    public function currentAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_to_employee_id');
    }
}
