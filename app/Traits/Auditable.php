<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Services\AuditEventService;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->auditEvent('created');
        });

        static::updated(function ($model) {
            $model->auditEvent('updated');
        });

        static::deleted(function ($model) {
            $model->auditEvent('deleted');
        });
    }

    protected function auditEvent(string $event): void
    {
        $changes = $this->getChanges();
        $oldValues = [];
        $newValues = [];
        foreach ($changes as $key => $value) {
            if (in_array($key, ['created_at', 'updated_at'], true)) {
                continue;
            }
            $oldValues[$key] = $this->getPrevious()[$key] ?? null;
            $newValues[$key] = $value;
        }
        if ($event === 'created') {
            $newValues = $this->getAttributes();
        } elseif ($event === 'deleted') {
            $oldValues = $this->getOriginal();
            $newValues = [];
        }

        app(AuditEventService::class)->record(
            module: strtolower(class_basename($this)),
            action: $event,
            entity: $this,
            description: class_basename($this).' '.$event,
            oldValues: $oldValues,
            newValues: $newValues,
        );
    }
}
