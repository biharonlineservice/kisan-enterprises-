<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Writes append-only audit entries for sensitive operations.
 */
class AuditLogger
{
    /** Attributes that must never be written to the audit trail. */
    private const REDACT = ['password', 'remember_token', 'bank_account_number', 'id_number'];

    public static function log(
        string $action,
        ?Model $subject = null,
        array $old = [],
        array $new = [],
        ?string $description = null,
        ?int $userId = null,
    ): AuditLog {
        $request = request();

        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'description' => $description ? mb_substr($description, 0, 255) : null,
            'old_values' => self::clean($old) ?: null,
            'new_values' => self::clean($new) ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
        ]);
    }

    /** Log the dirty attributes of a model that is about to be / was just saved. */
    public static function changes(string $action, Model $model, array $original, ?string $description = null): ?AuditLog
    {
        $changes = Arr::except($model->getChanges(), ['updated_at', 'created_at']);
        if (empty($changes)) {
            return null;
        }
        $old = Arr::only($original, array_keys($changes));

        return self::log($action, $model, $old, $changes, $description);
    }

    private static function clean(array $values): array
    {
        foreach (self::REDACT as $field) {
            if (array_key_exists($field, $values)) {
                $values[$field] = '[redacted]';
            }
        }

        return $values;
    }
}
