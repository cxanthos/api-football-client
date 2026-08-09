<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * `GET /teams` response item. `venue` has no `country` field of its own — see `Venue`.
 */
final readonly class Team
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $code,
        public ?string $country,
        public ?int $founded,
        public bool $national,
        public ?string $logo,
        public Venue $venue,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $team = Scalars::toMap($data['team'] ?? null);

        return new self(
            id: Scalars::toInt($team['id'] ?? null),
            name: Scalars::toString($team['name'] ?? null),
            code: Scalars::toStringOrNull($team['code'] ?? null),
            country: Scalars::toStringOrNull($team['country'] ?? null),
            founded: Scalars::toIntOrNull($team['founded'] ?? null),
            national: Scalars::toBool($team['national'] ?? false),
            logo: Scalars::toStringOrNull($team['logo'] ?? null),
            venue: Venue::fromArray(Scalars::toMap($data['venue'] ?? null)),
        );
    }
}
