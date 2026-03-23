<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use DateTimeInterface;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

/**
 * Formats log records as JSON following the Elastic Common Schema (ECS) v8.
 *
 * Fields: @timestamp, log.level, message, ecs.version, service.name,
 * service.environment, error.* (when exception context present).
 *
 * @see https://www.elastic.co/guide/en/ecs/current/index.html
 */
final class EcsFormatter extends NormalizerFormatter
{
    public function __construct()
    {
        parent::__construct(DateTimeInterface::RFC3339_EXTENDED);
    }

    public function format(LogRecord $record): string
    {
        $normalized = parent::format($record);

        $ecs = [
            '@timestamp' => $normalized['datetime'],
            'log.level' => mb_strtolower($record->level->name),
            'message' => $record->message,
            'ecs.version' => '8.11.0',
            'service.name' => config('app.name', 'laravel'),
            'service.environment' => config('app.env', 'production'),
        ];

        // Add context fields
        if ($normalized['context'] !== []) {
            // Extract exception if present
            if (isset($normalized['context']['exception'])) {
                $exception = $normalized['context']['exception'];
                $ecs['error.type'] = $exception['class'] ?? 'Exception';
                $ecs['error.message'] = $exception['message'] ?? '';
                if (isset($exception['file'], $exception['line'])) {
                    $ecs['error.stack_trace'] = sprintf('%s:%d', $exception['file'], $exception['line']);
                }
                if (isset($exception['trace'])) {
                    $ecs['error.stack_trace'] = $exception['trace'];
                }
                unset($normalized['context']['exception']);
            }

            // Remaining context as labels
            if ($normalized['context'] !== []) {
                $ecs['labels'] = $normalized['context'];
            }
        }

        // Add extra fields
        if ($normalized['extra'] !== []) {
            $ecs['metadata'] = $normalized['extra'];
        }

        // Add channel
        $ecs['log.logger'] = $record->channel;

        return json_encode($ecs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
    }

    public function formatBatch(array $records): string
    {
        $formatted = '';
        foreach ($records as $record) {
            $formatted .= $this->format($record);
        }

        return $formatted;
    }
}
