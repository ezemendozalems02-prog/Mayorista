<?php

namespace App\Traits;

use App\Services\MonitoringService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            self::logAudit($model, 'created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getChanges();
            
            // Only log if something actually changed
            if (!empty($newValues)) {
                self::logAudit($model, 'updated', $oldValues, $newValues);
            }
        });

        static::deleted(function (Model $model) {
            self::logAudit($model, 'deleted', $model->getAttributes(), null);
        });
    }

    protected static function logAudit(Model $model, string $action, $oldValues, $newValues)
    {
        $module = strtolower(class_basename($model));
        
        MonitoringService::audit(
            $module,
            $action,
            $model,
            $oldValues,
            $newValues
        );

        // Also log to file!
        MonitoringService::log(
            $module,
            "ID:{$model->id} has been {$action}",
            'info',
            ['new' => $newValues]
        );
    }
}
