<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/** `GET /players/teams` response item — a club the player belonged to, and which seasons there. */
final readonly class PlayerTeamSeasons
{
    /**
     * @param list<int> $seasons
     */
    public function __construct(
        public TeamRef $team,
        public array $seasons,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $seasons = Scalars::toArray($data['seasons'] ?? null);

        return new self(
            team: TeamRef::fromArray(Scalars::toMap($data['team'] ?? null)),
            seasons: array_values(array_map(
                static fn(mixed $year): int => Scalars::toInt($year),
                $seasons,
            )),
        );
    }
}
