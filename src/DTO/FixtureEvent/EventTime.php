<?php

declare(strict_types=1);

namespace ApiFootball\DTO\FixtureEvent;

use ApiFootball\Internal\Scalars;

final readonly class EventTime
{
    public function __construct(
        public ?int $elapsed,
        public ?int $extra,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            elapsed: Scalars::toIntOrNull($data['elapsed'] ?? null),
            extra: Scalars::toIntOrNull($data['extra'] ?? null),
        );
    }
}
