<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

/** A bare home/away pair with no `total` field — used for `biggest.goals.for`/`against`. */
final readonly class HomeAwayPair
{
    public function __construct(
        public ?int $home,
        public ?int $away,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            home: Scalars::toIntOrNull($data['home'] ?? null),
            away: Scalars::toIntOrNull($data['away'] ?? null),
        );
    }
}
