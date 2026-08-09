<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

final readonly class BiggestStreak
{
    public function __construct(
        public ?int $wins,
        public ?int $draws,
        public ?int $loses,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            wins: Scalars::toIntOrNull($data['wins'] ?? null),
            draws: Scalars::toIntOrNull($data['draws'] ?? null),
            loses: Scalars::toIntOrNull($data['loses'] ?? null),
        );
    }
}
