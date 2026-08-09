<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Lineup;

use ApiFootball\Internal\Scalars;

/** The `team` block of a `GET /fixtures/lineups` response item — a `TeamRef` plus kit colors. */
final readonly class LineupTeam
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $logo,
        public TeamColors $colors,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toInt($data['id'] ?? null),
            name: Scalars::toString($data['name'] ?? null),
            logo: Scalars::toStringOrNull($data['logo'] ?? null),
            colors: TeamColors::fromArray(Scalars::toMap($data['colors'] ?? null)),
        );
    }
}
