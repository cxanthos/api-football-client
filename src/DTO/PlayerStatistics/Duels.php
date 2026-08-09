<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

final readonly class Duels
{
    public function __construct(
        public ?int $total,
        public ?int $won,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: Scalars::toIntOrNull($data['total'] ?? null),
            won: Scalars::toIntOrNull($data['won'] ?? null),
        );
    }
}
