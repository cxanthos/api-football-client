<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\DTO\Standing\StandingRow;
use ApiFootball\Internal\Scalars;

/**
 * `GET /standings` response item. `standings` is an array of *groups* (each itself a list of rows) — e.g.
 * separate groups for a regular season table vs. a Champions-League-style group stage, not a flat list
 * (docs/design/endpoint-catalog.md).
 */
final readonly class Standings
{
    /**
     * @param list<list<StandingRow>> $standings
     */
    public function __construct(
        public LeagueRef $league,
        public array $standings,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $league = Scalars::toMap($data['league'] ?? null);
        $groups = Scalars::toArray($league['standings'] ?? null);

        return new self(
            league: LeagueRef::fromArray($league),
            standings: array_values(array_map(
                static fn(mixed $group): array => array_values(array_map(
                    static fn(mixed $row): StandingRow => StandingRow::fromArray(Scalars::toMap($row)),
                    Scalars::toArray($group),
                )),
                $groups,
            )),
        );
    }
}
