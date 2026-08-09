<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Standing;

use ApiFootball\DTO\TeamRef;
use ApiFootball\Internal\Scalars;

final readonly class StandingRow
{
    public function __construct(
        public int $rank,
        public TeamRef $team,
        public ?int $points,
        public ?int $goalsDiff,
        public ?string $group,
        public ?string $form,
        public ?string $status,
        public ?string $description,
        public StandingRecord $all,
        public StandingRecord $home,
        public StandingRecord $away,
        public ?string $update,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            rank: Scalars::toInt($data['rank'] ?? null),
            team: TeamRef::fromArray(Scalars::toMap($data['team'] ?? null)),
            points: Scalars::toIntOrNull($data['points'] ?? null),
            goalsDiff: Scalars::toIntOrNull($data['goalsDiff'] ?? null),
            group: Scalars::toStringOrNull($data['group'] ?? null),
            form: Scalars::toStringOrNull($data['form'] ?? null),
            status: Scalars::toStringOrNull($data['status'] ?? null),
            description: Scalars::toStringOrNull($data['description'] ?? null),
            all: StandingRecord::fromArray(Scalars::toMap($data['all'] ?? null)),
            home: StandingRecord::fromArray(Scalars::toMap($data['home'] ?? null)),
            away: StandingRecord::fromArray(Scalars::toMap($data['away'] ?? null)),
            update: Scalars::toStringOrNull($data['update'] ?? null),
        );
    }
}
