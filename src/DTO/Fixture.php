<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\DTO\Fixture\FixtureDetails;
use ApiFootball\DTO\Fixture\Score;
use ApiFootball\DTO\Fixture\Teams;
use ApiFootball\Internal\Scalars;

/**
 * `GET /fixtures` and `GET /fixtures/headtohead` response item — both endpoints return the identical
 * shape (docs/design/endpoint-catalog.md). No formal schema exists for this endpoint in the live spec;
 * this shape is transcribed from the spec's own worked example.
 */
final readonly class Fixture
{
    public function __construct(
        public FixtureDetails $fixture,
        public LeagueRef $league,
        public Teams $teams,
        public HomeAwayPair $goals,
        public Score $score,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fixture: FixtureDetails::fromArray(Scalars::toMap($data['fixture'] ?? null)),
            league: LeagueRef::fromArray(Scalars::toMap($data['league'] ?? null)),
            teams: Teams::fromArray(Scalars::toMap($data['teams'] ?? null)),
            goals: HomeAwayPair::fromArray(Scalars::toMap($data['goals'] ?? null)),
            score: Score::fromArray(Scalars::toMap($data['score'] ?? null)),
        );
    }
}
