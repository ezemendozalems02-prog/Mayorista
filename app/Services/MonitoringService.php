<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class MonitoringService
{
    /**
     * Log modular y por organización en archivos
     */
    public static function log(string $module, string $message, string $level = 'info', array $context = [])
    {
        $orgId = auth()->check() ? auth()->user()->organization_id : 'system';
        $userId = auth()->id() ?: 'guest';
        $date = now()->format('Y-m-d');
        
        // Estructura: storage/logs/{module}/{org_id}/{date}.log
        $directory = storage_path("logs/modules/{$module}/org_{$orgId}");
        
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $logPath = $directory . "/{$date}.log";
        $timestamp = now()->format('H:i:s');
        
        $formattedContext = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[{$timestamp}] [" . strtoupper($level) . "] User: {$userId} | Msg: {$message} {$formattedContext}" . PHP_EOL;

        File::append($logPath, $logMessage);
    }

    /**
     * Registro de auditoría en Base de Datos
     */
    public static function audit(
        string $module, 
        string $action, 
        $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ) {
        AuditLog::create([
            'organization_id' => auth()->user()?->organization_id,
            'user_id'         => auth()->id(),
            'module'          => $module,
            'action'          => $action,
            'model_type'      => $model ? get_class($model) : null,
            'model_id'        => $model ? $model->id : null,
            'old_values'      => $oldValues,
            'new_values'      => $newValues,
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
            'url'             => request()->fullUrl(),
        ]);
    }
}
