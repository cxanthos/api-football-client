<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * The venue block embedded in `GET /teams` responses. No `/venues` resource in MVP (docs/design/sdk-design.md
 * §2.1) — this is the only place venue data comes from.
 */
final readonly class Venue
{
    public function __construct(
        public int $id,
        public ?string $name,
        public ?string $address,
        public ?string $city,
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
            capacity: Scalars::toIntOrNull($data['capacity'] ?? null),
            surface: Scalars::toStringOrNull($data['surface'] ?? null),
            image: Scalars::toStringOrNull($data['image'] ?? null),
        );
    }
}
