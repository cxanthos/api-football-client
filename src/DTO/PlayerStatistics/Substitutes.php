<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

final readonly class Substitutes
{
    public function __construct(
        public ?int $in,
        public ?int $out,
        public ?int $bench,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            in: Scalars::toIntOrNull($data['in'] ?? null),
            out: Scalars::toIntOrNull($data['out'] ?? null),
            bench: Scalars::toIntOrNull($data['bench'] ?? null),
        );
    }
}
