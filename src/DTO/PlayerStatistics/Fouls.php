<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

final readonly class Fouls
{
    public function __construct(
        public ?int $drawn,
        public ?int $committed,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            drawn: Scalars::toIntOrNull($data['drawn'] ?? null),
            committed: Scalars::toIntOrNull($data['committed'] ?? null),
        );
    }
}
