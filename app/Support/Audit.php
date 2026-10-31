<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class Audit
{
    /**
     * Log an administrative action.
     *
     * @param string $action The action name (e.g., 'user_suspended')
     * @param mixed $target The model being affected
     * @param array|null $changes Old vs New values
     * @return AuditLog
     */
    public static function log(string $action, $target = null, ?array $changes = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'target_type' => $target ? get_class($target) : null,
            'target_id' => $target ? $target->id : null,
            'changes' => $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
