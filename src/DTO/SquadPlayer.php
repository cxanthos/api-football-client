<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/** One entry in `GET /players/squads`' `players[]` — a roster slot, not season statistics. */
final readonly class SquadPlayer
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $age,
        public ?int $number,
        public ?string $position,
        public ?string $photo,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toInt($data['id'] ?? null),
            name: Scalars::toString($data['name'] ?? null),
            age: Scalars::toIntOrNull($data['age'] ?? null),
            number: Scalars::toIntOrNull($data['number'] ?? null),
            position: Scalars::toStringOrNull($data['position'] ?? null),
            photo: Scalars::toStringOrNull($data['photo'] ?? null),
        );
    }
}
