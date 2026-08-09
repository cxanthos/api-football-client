<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\DTO\Lineup\LineupCoach;
use ApiFootball\DTO\Lineup\LineupSlot;
use ApiFootball\DTO\Lineup\LineupTeam;
use ApiFootball\Internal\Scalars;

/**
 * `GET /fixtures/lineups` response item — one team's starting XI, bench, formation, and coach for a
 * fixture (Tier 2). Building blocks live under `DTO\Lineup\*`.
 */
final readonly class Lineup
{
    /**
     * @param list<LineupSlot> $startXI
     * @param list<LineupSlot> $substitutes
     */
    public function __construct(
        public LineupTeam $team,
        public ?string $formation,
        public array $startXI,
        public array $substitutes,
        public LineupCoach $coach,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $startXI = Scalars::toArray($data['startXI'] ?? null);
        $substitutes = Scalars::toArray($data['substitutes'] ?? null);

        return new self(
            team: LineupTeam::fromArray(Scalars::toMap($data['team'] ?? null)),
            formation: Scalars::toStringOrNull($data['formation'] ?? null),
            startXI: array_values(array_map(
                static fn(mixed $slot): LineupSlot => LineupSlot::fromArray(Scalars::toMap($slot)),
                $startXI,
            )),
            substitutes: array_values(array_map(
                static fn(mixed $slot): LineupSlot => LineupSlot::fromArray(Scalars::toMap($slot)),
                $substitutes,
            )),
            coach: LineupCoach::fromArray(Scalars::toMap($data['coach'] ?? null)),
        );
    }
}
