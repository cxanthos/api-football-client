<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Fixture;

use ApiFootball\Internal\Scalars;

/** One side (`teams.home`/`teams.away`) — the same `{id, name, logo}` shape as `TeamRef` plus `winner`. */
final readonly class Participant
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $logo,
        public ?bool $winner,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toInt($data['id'] ?? null),
            name: Scalars::toString($data['name'] ?? null),
            logo: Scalars::toStringOrNull($data['logo'] ?? null),
            winner: Scalars::toBoolOrNull($data['winner'] ?? null),
        );
    }
}
