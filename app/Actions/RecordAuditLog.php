<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AuditLog;
use Illuminate\Http\Request;

final readonly class RecordAuditLog
{
    public function handle(
        string $action,
        ?string $subjectType = null,
        string|int|null $subjectId = null,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?int $organizationId = null,
        ?int $actorId = null,
        string $actorType = 'user',
        ?string $ipAddress = null,
    ): AuditLog {
        $request = resolve(Request::class);

        return AuditLog::query()->create([
            'organization_id' => $organizationId,
            'actor_id' => $actorId ?? auth()->id(),
            'actor_type' => $actorType,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId !== null ? (string) $subjectId : null,
            'old_value' => $oldValue !== null ? (is_array($oldValue) ? $oldValue : ['value' => $oldValue]) : null,
            'new_value' => $newValue !== null ? (is_array($newValue) ? $newValue : ['value' => $newValue]) : null,
            'ip_address' => $ipAddress ?? $request->ip(),
            'created_at' => now(),
        ]);
    }
}
