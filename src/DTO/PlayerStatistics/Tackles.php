<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

final readonly class Tackles
{
    public function __construct(
        public ?int $total,
        public ?int $blocks,
        public ?int $interceptions,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: Scalars::toIntOrNull($data['total'] ?? null),
            blocks: Scalars::toIntOrNull($data['blocks'] ?? null),
            interceptions: Scalars::toIntOrNull($data['interceptions'] ?? null),
        );
    }
}
