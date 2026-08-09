<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

final readonly class Dribbles
{
    public function __construct(
        public ?int $attempts,
        public ?int $success,
        public ?int $past,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            attempts: Scalars::toIntOrNull($data['attempts'] ?? null),
            success: Scalars::toIntOrNull($data['success'] ?? null),
            past: Scalars::toIntOrNull($data['past'] ?? null),
        );
    }
}
