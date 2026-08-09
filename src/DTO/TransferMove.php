<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/** One entry in a player's `transfers[]` — a single move between clubs. */
final readonly class TransferMove
{
    public function __construct(
        public ?string $date,
        public ?string $type,
        public TeamRef $teamIn,
        public TeamRef $teamOut,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $teams = Scalars::toMap($data['teams'] ?? null);

        return new self(
            date: Scalars::toStringOrNull($data['date'] ?? null),
            type: Scalars::toStringOrNull($data['type'] ?? null),
            teamIn: TeamRef::fromArray(Scalars::toMap($teams['in'] ?? null)),
            teamOut: TeamRef::fromArray(Scalars::toMap($teams['out'] ?? null)),
        );
    }
}
