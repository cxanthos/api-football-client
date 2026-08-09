<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * Shared by the venue block embedded in `GET /teams` responses and `GET /venues` itself (Tier 2).
 * `country` is only ever populated from the latter — `/teams`' embedded venue has no `country` field of
 * its own, so it's simply null in that context, not a separate DTO.
 */
final readonly class Venue
{
    public function __construct(
        public int $id,
        public ?string $name,
        public ?string $address,
        public ?string $city,
        public ?string $country,
        public ?int $capacity,
        public ?string $surface,
        public ?string $image,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toInt($data['id'] ?? null),
            name: Scalars::toStringOrNull($data['name'] ?? null),
            address: Scalars::toStringOrNull($data['address'] ?? null),
            city: Scalars::toStringOrNull($data['city'] ?? null),
            country: Scalars::toStringOrNull($data['country'] ?? null),
            capacity: Scalars::toIntOrNull($data['capacity'] ?? null),
            surface: Scalars::toStringOrNull($data['surface'] ?? null),
            image: Scalars::toStringOrNull($data['image'] ?? null),
        );
    }
}
