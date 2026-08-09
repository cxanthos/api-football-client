<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Fixture;

use ApiFootball\DTO\FixtureStatus;
use ApiFootball\Internal\Scalars;

/** The `fixture` block of a `GET /fixtures`-shaped response — not to be confused with the top-level
 * `Fixture` DTO, which wraps this alongside `league`/`teams`/`goals`/`score`. */
final readonly class FixtureDetails
{
    public function __construct(
        public int $id,
        public ?string $referee,
        public string $timezone,
        public string $date,
        public int $timestamp,
        public Periods $periods,
        public VenueRef $venue,
        public FixtureStatus $status,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toInt($data['id'] ?? null),
            referee: Scalars::toStringOrNull($data['referee'] ?? null),
            timezone: Scalars::toString($data['timezone'] ?? null),
            date: Scalars::toString($data['date'] ?? null),
            timestamp: Scalars::toInt($data['timestamp'] ?? null),
            periods: Periods::fromArray(Scalars::toMap($data['periods'] ?? null)),
            venue: VenueRef::fromArray(Scalars::toMap($data['venue'] ?? null)),
            status: FixtureStatus::fromArray(Scalars::toMap($data['status'] ?? null)),
        );
    }
}
