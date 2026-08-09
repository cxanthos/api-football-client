<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

final readonly class Cards
{
    public function __construct(
        public ?int $yellow,
        public ?int $yellowred,
        public ?int $red,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            yellow: Scalars::toIntOrNull($data['yellow'] ?? null),
            yellowred: Scalars::toIntOrNull($data['yellowred'] ?? null),
            red: Scalars::toIntOrNull($data['red'] ?? null),
        );
    }
}
