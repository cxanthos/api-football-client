<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * A bare home/away pair with no `total` field. Reused across team-statistics (`biggest.goals.for`/`against`)
 * and fixtures (`goals`, each `score.*` split).
 */
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
