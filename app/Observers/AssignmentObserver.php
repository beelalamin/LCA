<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Services\AuditLogger;

class AssignmentObserver
{
    /**
     * Handle the Assignment "created" event.
     */
    public function created(Assignment $assignment): void
    {
        AuditLogger::log('CHECKED_OUT', $assignment, null, $assignment->toArray());
    }

    /**
     * Handle the Assignment "updated" event.
     */
    public function updated(Assignment $assignment): void
    {
        if ($assignment->isDirty('checked_in_at') && $assignment->checked_in_at !== null) {
            AuditLogger::log('CHECKED_IN', $assignment, ['is_active' => true], ['is_active' => false]);
            // The assignment service handles setting is_active=false manually
        }
    }

    /**
     * Handle the Assignment "deleted" event.
     */
    public function deleted(Assignment $assignment): void
    {
        //
    }

    /**
     * Handle the Assignment "restored" event.
     */
    public function restored(Assignment $assignment): void
    {
        //
    }

    /**
     * Handle the Assignment "force deleted" event.
     */
    public function forceDeleted(Assignment $assignment): void
    {
        //
    }
}
