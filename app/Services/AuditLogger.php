<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public static function log(string $action, Model $entity, ?array $oldValues, ?array $newValues, ?string $userId = null): void
    {
        $performedBy = $userId ?? auth()->id();
        
        // Fallback to the entity's creator or first admin if specifically requested or in console
        if (!$performedBy && app()->runningInConsole()) {
             // Optional: Find first admin or specific system user ID
             // For now, we'll allow null and handle it in the UI as 'System'
        }

        AuditLog::create([
            'asset_id' => $entity->asset_id ?? ($entity->id ?? null),
            'performed_by' => $performedBy,
            'performed_at' => now(),
            'action' => $action,
            'entity_type' => get_class($entity),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ]);
    }
}
