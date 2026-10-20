<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditEventService
{
    private const REDACTED_KEYS = [
        'password', 'password_confirmation', 'remember_token', 'token', 'secret',
        'authorization', 'cookie', 'private_key', 'api_key',
    ];

    public function record(
        string $module,
        string $action,
        ?Model $entity = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $result = 'success',
        ?string $reason = null,
        ?Request $request = null,
        ?array $context = null,
    ): AuditLog {
        $request ??= request();
        $oldValues = $this->sanitize($oldValues ?? []);
        $newValues = $this->sanitize($newValues ?? []);
        $context = $this->sanitize($context ?? []);
        $agent = (string) $request->userAgent();
        $device = $this->deviceDetails($agent);

        return DB::transaction(function () use (
            $module, $action, $entity, $description, $oldValues, $newValues, $result, $reason,
            $request, $context, $agent, $device
        ): AuditLog {
            $previousHash = AuditLog::query()->whereNotNull('event_hash')->latest('id')->lockForUpdate()->value('event_hash');
            $entityType = $entity ? $entity::class : 'SystemEvent';
            $entityId = $entity ? (string) $entity->getKey() : '0';
            $log = AuditLog::create([
                'user_id' => $request->user()?->getAuthIdentifier(),
                'auditable_type' => $entityType,
                'auditable_id' => is_numeric($entityId) ? (int) $entityId : 0,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'event' => $action,
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'old_values' => $oldValues ?: null,
                'new_values' => $newValues ?: null,
                'result' => $result,
                'reason' => $reason,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit($agent, 1000, ''),
                'browser' => $device['browser'],
                'operating_system' => $device['operating_system'],
                'device_type' => $device['device_type'],
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'correlation_id' => $request->headers->get('X-Correlation-ID') ?: (string) Str::uuid(),
                'route_name' => $request->route()?->getName(),
                'context' => $context,
                'previous_hash' => $previousHash,
            ]);

            $log->forceFill(['event_hash' => $this->hashLog($log)])->saveQuietly();

            return $log;
        });
    }

    public function verifyChain(): array
    {
        $previousHash = null;
        $checked = 0;
        $invalidIds = [];
        foreach (AuditLog::query()->whereNotNull('event_hash')->orderBy('id')->cursor() as $log) {
            if ($log->previous_hash !== $previousHash || ! hash_equals((string) $log->event_hash, $this->hashLog($log))) {
                $invalidIds[] = $log->id;
            }
            $previousHash = $log->event_hash;
            $checked++;
        }

        return ['checked' => $checked, 'invalid_ids' => $invalidIds];
    }

    private function sanitize(array $values): array
    {
        $safe = [];
        foreach ($values as $key => $value) {
            if (in_array(strtolower((string) $key), self::REDACTED_KEYS, true)
                || str_contains(strtolower((string) $key), 'password')
                || str_contains(strtolower((string) $key), 'token')) {
                continue;
            }
            $safe[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $safe;
    }

    private function deviceDetails(string $agent): array
    {
        $browser = match (true) {
            preg_match('/Edg\/([\d.]+)/i', $agent, $m) === 1 => 'Edge '.$m[1],
            preg_match('/Chrome\/([\d.]+)/i', $agent, $m) === 1 => 'Chrome '.$m[1],
            preg_match('/Firefox\/([\d.]+)/i', $agent, $m) === 1 => 'Firefox '.$m[1],
            preg_match('/Version\/([\d.]+).*Safari/i', $agent, $m) === 1 => 'Safari '.$m[1],
            default => $agent === '' ? null : 'Navegador no identificado',
        };
        $operatingSystem = match (true) {
            str_contains(strtolower($agent), 'windows') => 'Windows',
            str_contains(strtolower($agent), 'mac os') || str_contains(strtolower($agent), 'macintosh') => 'macOS',
            str_contains(strtolower($agent), 'android') => 'Android',
            str_contains(strtolower($agent), 'iphone') || str_contains(strtolower($agent), 'ipad') => 'iOS',
            str_contains(strtolower($agent), 'linux') => 'Linux',
            default => $agent === '' ? null : 'Sistema no identificado',
        };

        return [
            'browser' => $browser,
            'operating_system' => $operatingSystem,
            'device_type' => preg_match('/tablet|ipad/i', $agent) ? 'tablet' : (preg_match('/mobile|iphone|android/i', $agent) ? 'mobile' : 'desktop'),
        ];
    }

    private function hashLog(AuditLog $log): string
    {
        return hash('sha256', json_encode([
            'previous_hash' => $log->previous_hash,
            'id' => $log->id,
            'user_id' => $log->user_id,
            'auditable_type' => $log->auditable_type,
            'auditable_id' => $log->auditable_id,
            'entity_type' => $log->entity_type,
            'entity_id' => $log->entity_id,
            'event' => $log->event,
            'module' => $log->module,
            'action' => $log->action,
            'description' => $log->description,
            'old_values' => $this->canonicalize($log->old_values),
            'new_values' => $this->canonicalize($log->new_values),
            'result' => $log->result,
            'reason' => $log->reason,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'browser' => $log->browser,
            'operating_system' => $log->operating_system,
            'device_type' => $log->device_type,
            'session_id' => $log->session_id,
            'correlation_id' => $log->correlation_id,
            'route_name' => $log->route_name,
            'context' => $this->canonicalize($log->context),
            'created_at' => $log->created_at?->toISOString(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as &$item) {
            $item = $this->canonicalize($item);
        }
        unset($item);

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }
}
