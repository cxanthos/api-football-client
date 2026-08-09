<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/** `GET /fixtures/statistics` response item — one team's match statistics. */
final readonly class FixtureTeamStatistics
{
    /**
     * @param list<StatItem> $statistics
     */
    public function __construct(
        public TeamRef $team,
        public array $statistics,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $statistics = Scalars::toArray($data['statistics'] ?? null);

        return new self(
            team: TeamRef::fromArray(Scalars::toMap($data['team'] ?? null)),
            statistics: array_values(array_map(
                static fn(mixed $item): StatItem => StatItem::fromArray(Scalars::toMap($item)),
                $statistics,
            )),
        );
    }
}
