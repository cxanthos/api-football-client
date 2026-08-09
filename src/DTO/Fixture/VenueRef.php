<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Fixture;

use ApiFootball\Internal\Scalars;

/** The minimal `{id, name, city}` venue reference embedded in fixture responses — distinct from the full
 * `Venue` DTO returned by `GET /teams`, which also carries address/capacity/surface/image. */
final readonly class VenueRef
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $city,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toIntOrNull($data['id'] ?? null),
            name: Scalars::toStringOrNull($data['name'] ?? null),
            city: Scalars::toStringOrNull($data['city'] ?? null),
        );
    }
}
