<?php

declare(strict_types=1);

namespace ApiFootball\DTO\FixtureTeamPlayers;

use ApiFootball\Internal\Scalars;

/**
 * Distinct shape from `PlayerStatistics\Games` — match context has `substitute` (did they come off the
 * bench this match) but no `appearences`/`lineups` (those are season-level aggregates).
 */
final readonly class MatchGames
{
    public function __construct(
        public ?int $minutes,
        public ?int $number,
        public ?string $position,
        public ?string $rating,
        public bool $captain,
        public bool $substitute,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            minutes: Scalars::toIntOrNull($data['minutes'] ?? null),
            number: Scalars::toIntOrNull($data['number'] ?? null),
            position: Scalars::toStringOrNull($data['position'] ?? null),
            rating: Scalars::toStringOrNull($data['rating'] ?? null),
            captain: Scalars::toBool($data['captain'] ?? false),
            substitute: Scalars::toBool($data['substitute'] ?? false),
        );
    }
}
