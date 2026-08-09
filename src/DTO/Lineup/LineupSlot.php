<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Lineup;

use ApiFootball\Internal\Scalars;

/** One entry in `startXI[]`/`substitutes[]` — the API wraps each player in its own `{"player": {...}}`. */
final readonly class LineupSlot
{
    public function __construct(
        public LineupPlayer $player,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            player: LineupPlayer::fromArray(Scalars::toMap($data['player'] ?? null)),
        );
    }
}
