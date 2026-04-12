<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            static::logChange($model, 'created');
        });

        static::updated(function ($model) {
            static::logChange($model, 'updated');
        });

        static::deleted(function ($model) {
            static::logChange($model, 'deleted');
        });
    }

    protected static function logChange($model, $action)
    {
        $user = Auth::user();
        $changes = [];
        $modelName = class_basename($model);

        if ($action === 'updated') {
            foreach ($model->getDirty() as $key => $value) {
                // Skip sensitive or redundant fields
                if (in_array($key, ['password', 'remember_token', 'updated_at', 'last_login_at'])) continue;
                
                $oldValue = $model->getOriginal($key);
                
                // Only log if values actually differ (handling nulls and types)
                if ($oldValue != $value) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $value,
                    ];
                }
            }
        } elseif ($action === 'created') {
            $changes = $model->getAttributes();
            unset($changes['password'], $changes['remember_token'], $changes['created_at'], $changes['updated_at']);
        } elseif ($action === 'deleted') {
            $changes = $model->getOriginal();
            unset($changes['password'], $changes['remember_token']);
        }

        // Don't log updates if no fields actually changed
        if ($action === 'updated' && empty($changes)) {
            return;
        }

        AuditLog::create([
            'user_id' => $user->id ?? null,
            'user_name' => $user->username ?? 'System',
            'action' => $action,
            'entity_type' => $modelName,
            'entity_id' => $model->id ?? null,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'metadata' => json_encode($changes, JSON_UNESCAPED_UNICODE),
            'description' => ucfirst($action) . " {$modelName} entry.",
            'timestamp' => now()
        ]);
    }
}
