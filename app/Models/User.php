<?php

namespace App\Models;

use App\Models\Lookups\Department;
use App\Models\Lookups\EmploymentType;
use App\Models\Lookups\JobTitle;
use App\Models\Lookups\OfficeLocation;
use App\Models\Lookups\Status;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasName, FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, HasPanelShield;

    protected $fillable = [
        'employee_number',
        'full_name',
        'full_name_ar',
        'email',
        'password',
        'preferred_locale',
        'is_active',
        'photo_path',
        'phone',
        'department_id',
        'job_title_id',
        'employment_type_id',
        'office_location_id',
        'line_manager_id',
        'status_id',
        'joining_date',
        'leaving_date',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'joining_date' => 'date',
            'leaving_date' => 'date',
        ];
    }

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
        return $this->belongsTo(User::class, 'line_manager_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_to_user_id');
    }

    public function currentAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_to_user_id');
    }

    public function createdAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'created_by');
    }

    public function performedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'assigned_by');
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class, 'technician_id');
    }

    public function getFilamentName(): string
    {
        return $this->display_name;
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            return $this->full_name_ar ?: ($this->full_name ?: ($this->email ?? ''));
        }

        return $this->full_name ?: ($this->full_name_ar ?: ($this->email ?? ''));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
