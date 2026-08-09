<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Standing;

use ApiFootball\Internal\Scalars;

final readonly class StandingGoals
{
    public function __construct(
        public ?int $for,
        public ?int $against,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            for: Scalars::toIntOrNull($data['for'] ?? null),
            against: Scalars::toIntOrNull($data['against'] ?? null),
        );
    }
}
