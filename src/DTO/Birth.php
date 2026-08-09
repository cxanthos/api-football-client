<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/** Shared `{date, place, country}` shape — used by `PlayerProfile` and `Coach`. */
final readonly class Birth
{
    public function __construct(
        public ?string $date,
        public ?string $place,
        public ?string $country,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            date: Scalars::toStringOrNull($data['date'] ?? null),
            place: Scalars::toStringOrNull($data['place'] ?? null),
            country: Scalars::toStringOrNull($data['country'] ?? null),
        );
    }
}
