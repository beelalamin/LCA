<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class AssignmentObserver
{
    public function creating(Assignment $assignment): void
    {
        if (empty($assignment->assignment_number)) {
            $assignment->assignment_number = static::generateAssignmentNumber();
        }

        if ($assignment->employee_id) {
            $employee = $assignment->employee()->first();
            if ($employee) {
                if (empty($assignment->department_id) && $employee->department_id) {
                    $assignment->department_id = $employee->department_id;
                }
                if (empty($assignment->office_location_id) && $employee->office_location_id) {
                    $assignment->office_location_id = $employee->office_location_id;
                }
            }
        }
    }

    public function created(Assignment $assignment): void
    {
        $this->mirrorToAsset($assignment);

        AuditLogger::log('CHECKED_OUT', $assignment, null, $assignment->toArray());
    }

    public function updated(Assignment $assignment): void
    {
        $this->mirrorToAsset($assignment);

        if ($assignment->isDirty('checked_in_at') && $assignment->checked_in_at !== null) {
            AuditLogger::log('CHECKED_IN', $assignment, ['is_active' => true], ['is_active' => false]);
        }
    }

    public function deleted(Assignment $assignment): void
    {
    }

    public function restored(Assignment $assignment): void
    {
    }

    public function forceDeleted(Assignment $assignment): void
    {
    }

    protected function mirrorToAsset(Assignment $assignment): void
    {
        $asset = $assignment->asset;

        if (! $asset) {
            return;
        }

        $update = [
            'assigned_to_employee_id' => $assignment->is_active ? $assignment->employee_id : null,
            'assignment_status_id' => $assignment->assignment_status_id,
            'assigned_date' => $assignment->checked_out_at?->toDateString(),
            'return_date' => $assignment->checked_in_at?->toDateString(),
            'return_reason_id' => $assignment->return_reason_id,
        ];

        $optional = array_filter([
            'maintenance_status_id' => $assignment->maintenance_status_id,
            'maintenance_type_id' => $assignment->maintenance_type_id,
            'warranty_provider_id' => $assignment->warranty_provider_id,
        ], fn ($v) => $v !== null);

        $asset->forceFill(array_merge($update, $optional))->saveQuietly();
    }

    protected static function generateAssignmentNumber(): string
    {
        $year = date('Y');
        $count = DB::table('assignments')
            ->where('assignment_number', 'like', "ASG-{$year}-%")
            ->count();
        $sequence = str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        return "ASG-{$year}-{$sequence}";
    }
}
