<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

final readonly class LeagueSeason
{
    public function __construct(
        public int $year,
        public ?string $start,
        public ?string $end,
        public bool $current,
        public Coverage $coverage,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            year: Scalars::toInt($data['year'] ?? null),
            start: Scalars::toStringOrNull($data['start'] ?? null),
            end: Scalars::toStringOrNull($data['end'] ?? null),
            current: Scalars::toBool($data['current'] ?? false),
            coverage: Coverage::fromArray(Scalars::toMap($data['coverage'] ?? null)),
        );
    }
}
