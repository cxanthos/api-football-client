<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/** One entry in a coach's `career[]` — a club they managed and when. */
final readonly class CoachCareerEntry
{
    public function __construct(
        public TeamRef $team,
        public ?string $start,
        public ?string $end,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            team: TeamRef::fromArray(Scalars::toMap($data['team'] ?? null)),
            start: Scalars::toStringOrNull($data['start'] ?? null),
            end: Scalars::toStringOrNull($data['end'] ?? null),
        );
    }
}
