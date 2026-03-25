<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public static function log(string $action, Model $entity, ?array $oldValues, ?array $newValues, ?string $userId = null): void
    {
        AuditLog::create([
            'asset_id' => $entity->asset_id ?? ($entity->id ?? null),
            'performed_by' => $userId ?? auth()->id(),
            'action' => $action,
            'entity_type' => get_class($entity),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }
}
