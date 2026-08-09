<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\DTO\PlayerStatistics\Birth;
use ApiFootball\Internal\Scalars;

/**
 * The `player` block shared by `/players`, `/players/topscorers`, `/players/topassists`,
 * `/players/topyellowcards`, `/players/topredcards` — distinct from `PlayerRef`, the bare `{id, name}`
 * reference used in fixture events.
 */
final readonly class PlayerProfile
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $firstname,
        public ?string $lastname,
        public ?int $age,
        public Birth $birth,
        public ?string $nationality,
        public ?string $height,
        public ?string $weight,
        public bool $injured,
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
            firstname: Scalars::toStringOrNull($data['firstname'] ?? null),
            lastname: Scalars::toStringOrNull($data['lastname'] ?? null),
            age: Scalars::toIntOrNull($data['age'] ?? null),
            birth: Birth::fromArray(Scalars::toMap($data['birth'] ?? null)),
            nationality: Scalars::toStringOrNull($data['nationality'] ?? null),
            height: Scalars::toStringOrNull($data['height'] ?? null),
            weight: Scalars::toStringOrNull($data['weight'] ?? null),
            injured: Scalars::toBool($data['injured'] ?? false),
            photo: Scalars::toStringOrNull($data['photo'] ?? null),
        );
    }
}
