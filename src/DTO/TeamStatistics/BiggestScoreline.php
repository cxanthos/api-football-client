<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

/** A scoreline like `"4-0"` — used for `biggest.wins`/`biggest.loses`. Kept as a string, not parsed. */
final readonly class BiggestScoreline
{
    public function __construct(
        public ?string $home,
        public ?string $away,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            home: Scalars::toStringOrNull($data['home'] ?? null),
            away: Scalars::toStringOrNull($data['away'] ?? null),
        );
    }
}
