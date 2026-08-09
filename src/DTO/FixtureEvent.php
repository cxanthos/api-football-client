<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\DTO\FixtureEvent\EventTime;
use ApiFootball\Internal\Scalars;

/**
 * `GET /fixtures/events` response item — goals, cards, and substitutions with minute + assist
 * (docs/design/endpoint-catalog.md). `type`/`detail` are free-text from the API (e.g. "Goal"/"Normal Goal",
 * "Card"/"Yellow Card", "subst"/"Substitution 1") — not modeled as an enum since the live spec doesn't
 * document a closed set of values for either field.
 */
final readonly class FixtureEvent
{
    public function __construct(
        public EventTime $time,
        public TeamRef $team,
        public PlayerRef $player,
        public PlayerRef $assist,
        public string $type,
        public ?string $detail,
        public ?string $comments,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            time: EventTime::fromArray(Scalars::toMap($data['time'] ?? null)),
            team: TeamRef::fromArray(Scalars::toMap($data['team'] ?? null)),
            player: PlayerRef::fromArray(Scalars::toMap($data['player'] ?? null)),
            assist: PlayerRef::fromArray(Scalars::toMap($data['assist'] ?? null)),
            type: Scalars::toString($data['type'] ?? null),
            detail: Scalars::toStringOrNull($data['detail'] ?? null),
            comments: Scalars::toStringOrNull($data['comments'] ?? null),
        );
    }
}
