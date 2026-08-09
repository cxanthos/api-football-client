<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Standing;

use ApiFootball\Internal\Scalars;

/** One of a standing row's `all`/`home`/`away` splits. */
final readonly class StandingRecord
{
    public function __construct(
        public ?int $played,
        public ?int $win,
        public ?int $draw,
        public ?int $lose,
        public StandingGoals $goals,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            played: Scalars::toIntOrNull($data['played'] ?? null),
            win: Scalars::toIntOrNull($data['win'] ?? null),
            draw: Scalars::toIntOrNull($data['draw'] ?? null),
            lose: Scalars::toIntOrNull($data['lose'] ?? null),
            goals: StandingGoals::fromArray(Scalars::toMap($data['goals'] ?? null)),
        );
    }
}
