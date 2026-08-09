<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * `GET /players/profiles` response item. A genuinely different shape from `PlayerProfile` (the `player`
 * block shared by `/players`/topscorers/topassists/etc.) — this one has `number`/`position` but no
 * `injured`, so it's its own DTO rather than a variant.
 */
final readonly class ProfiledPlayer
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
        public ?int $number,
        public ?string $position,
        public ?string $photo,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $player = Scalars::toMap($data['player'] ?? null);

        return new self(
            id: Scalars::toInt($player['id'] ?? null),
            name: Scalars::toString($player['name'] ?? null),
            firstname: Scalars::toStringOrNull($player['firstname'] ?? null),
            lastname: Scalars::toStringOrNull($player['lastname'] ?? null),
            age: Scalars::toIntOrNull($player['age'] ?? null),
            birth: Birth::fromArray(Scalars::toMap($player['birth'] ?? null)),
            nationality: Scalars::toStringOrNull($player['nationality'] ?? null),
            height: Scalars::toStringOrNull($player['height'] ?? null),
            weight: Scalars::toStringOrNull($player['weight'] ?? null),
            number: Scalars::toIntOrNull($player['number'] ?? null),
            position: Scalars::toStringOrNull($player['position'] ?? null),
            photo: Scalars::toStringOrNull($player['photo'] ?? null),
        );
    }
}
