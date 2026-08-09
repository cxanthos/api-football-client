<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;
use InvalidArgumentException;

/**
 * A single league-season's `coverage` block from `GET /leagues` — what the manual, opt-in coverage helper
 * (docs/design/sdk-design.md §4.5) reads. Flags reflect availability *at call time* and can change as a
 * season progresses; nothing here is cached by the SDK.
 */
final readonly class Coverage
{
    public function __construct(
        public bool $fixtureEvents,
        public bool $fixtureLineups,
        public bool $fixtureStatisticsFixtures,
        public bool $fixtureStatisticsPlayers,
        public bool $standings,
        public bool $players,
        public bool $topScorers,
        public bool $topAssists,
        public bool $topCards,
        public bool $injuries,
        public bool $predictions,
        public bool $odds,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $fixtures = Scalars::toMap($data['fixtures'] ?? null);

        return new self(
            fixtureEvents: Scalars::toBool($fixtures['events'] ?? false),
            fixtureLineups: Scalars::toBool($fixtures['lineups'] ?? false),
            fixtureStatisticsFixtures: Scalars::toBool($fixtures['statistics_fixtures'] ?? false),
            fixtureStatisticsPlayers: Scalars::toBool($fixtures['statistics_players'] ?? false),
            standings: Scalars::toBool($data['standings'] ?? false),
            players: Scalars::toBool($data['players'] ?? false),
            topScorers: Scalars::toBool($data['top_scorers'] ?? false),
            topAssists: Scalars::toBool($data['top_assists'] ?? false),
            topCards: Scalars::toBool($data['top_cards'] ?? false),
            injuries: Scalars::toBool($data['injuries'] ?? false),
            predictions: Scalars::toBool($data['predictions'] ?? false),
            odds: Scalars::toBool($data['odds'] ?? false),
        );
    }

    /**
     * @param 'fixtures.events'|'fixtures.lineups'|'fixtures.statistics_fixtures'|'fixtures.statistics_players'|'standings'|'players'|'top_scorers'|'top_assists'|'top_cards'|'injuries'|'predictions'|'odds' $flag
     */
    public function supports(string $flag): bool
    {
        return match ($flag) {
            'fixtures.events' => $this->fixtureEvents,
            'fixtures.lineups' => $this->fixtureLineups,
            'fixtures.statistics_fixtures' => $this->fixtureStatisticsFixtures,
            'fixtures.statistics_players' => $this->fixtureStatisticsPlayers,
            'standings' => $this->standings,
            'players' => $this->players,
            'top_scorers' => $this->topScorers,
            'top_assists' => $this->topAssists,
            'top_cards' => $this->topCards,
            'injuries' => $this->injuries,
            'predictions' => $this->predictions,
            'odds' => $this->odds,
            default => throw new InvalidArgumentException("Unknown coverage flag: {$flag}"),
        };
    }
}
