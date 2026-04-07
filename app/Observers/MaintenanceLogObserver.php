<?php

namespace App\Observers;

use App\Models\MaintenanceLog;
use App\Services\AuditLogger;

class MaintenanceLogObserver
{
    /**
     * Handle the MaintenanceLog "created" event.
     */
    public function created(MaintenanceLog $log): void
    {
        AuditLogger::log(
            'MAINTENANCE_SCHEDULED', 
            $log, 
            null, 
            ['type' => $log->type, 'scheduled_date' => $log->scheduled_date?->toDateString()]
        );
    }

    /**
     * Handle the MaintenanceLog "updated" event.
     */
    public function updated(MaintenanceLog $log): void
    {
        if ($log->isDirty('status')) {
            $action = $log->status === 'COMPLETED' ? 'MAINTENANCE_COMPLETED' : 'MAINTENANCE_STATUS_CHANGED';
            AuditLogger::log(
                $action, 
                $log, 
                ['status' => $log->getOriginal('status')], 
                ['status' => $log->status, 'completed_date' => $log->completed_date?->toDateString()]
            );
        }
    }
}
