<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

/** `commited` (not `committed`) is the API's own field name — preserved faithfully, not "corrected". */
final readonly class Penalty
{
    public function __construct(
        public ?int $won,
        public ?int $commited,
        public ?int $scored,
        public ?int $missed,
        public ?int $saved,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            won: Scalars::toIntOrNull($data['won'] ?? null),
            commited: Scalars::toIntOrNull($data['commited'] ?? null),
            scored: Scalars::toIntOrNull($data['scored'] ?? null),
            missed: Scalars::toIntOrNull($data['missed'] ?? null),
            saved: Scalars::toIntOrNull($data['saved'] ?? null),
        );
    }
}
