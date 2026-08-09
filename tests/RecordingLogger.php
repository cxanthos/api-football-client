<?php

declare(strict_types=1);

namespace ApiFootball\Tests;

use Psr\Log\AbstractLogger;
use Stringable;

/** Records every log call instead of writing anywhere, so tests can assert on what got logged. */
final class RecordingLogger extends AbstractLogger
{
    /** @var list<array{level: mixed, message: string, context: array<int|string,mixed>}> */
    public array $records = [];

    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }

    /**
     * @return list<array{level: mixed, message: string, context: array<int|string,mixed>}>
     */
    public function recordsAtLevel(string $level): array
    {
        return array_values(array_filter($this->records, static fn(array $record): bool => $record['level'] === $level));
    }
}
