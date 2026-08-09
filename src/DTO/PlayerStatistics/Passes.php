<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

/** `accuracy` is a plain int here, unlike team-statistics' formatted-percentage strings. */
final readonly class Passes
{
    public function __construct(
        public ?int $total,
        public ?int $key,
        public ?int $accuracy,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: Scalars::toIntOrNull($data['total'] ?? null),
            key: Scalars::toIntOrNull($data['key'] ?? null),
            accuracy: Scalars::toIntOrNull($data['accuracy'] ?? null),
        );
    }
}
