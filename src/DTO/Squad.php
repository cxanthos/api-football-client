<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * `GET /players/squads` response item — a team's current roster (not historical, no season param exists
 * for this endpoint, docs/design/endpoint-catalog.md).
 */
final readonly class Squad
{
    /**
     * @param list<SquadPlayer> $players
     */
    public function __construct(
        public TeamRef $team,
        public array $players,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $players = Scalars::toArray($data['players'] ?? null);

        return new self(
            team: TeamRef::fromArray(Scalars::toMap($data['team'] ?? null)),
            players: array_values(array_map(
                static fn(mixed $item): SquadPlayer => SquadPlayer::fromArray(Scalars::toMap($item)),
                $players,
            )),
        );
    }
}
