<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

final readonly class Shots
{
    public function __construct(
        public ?int $total,
        public ?int $on,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: Scalars::toIntOrNull($data['total'] ?? null),
            on: Scalars::toIntOrNull($data['on'] ?? null),
        );
    }
}
