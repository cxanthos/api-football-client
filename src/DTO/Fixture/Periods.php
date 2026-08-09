<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Fixture;

use ApiFootball\Internal\Scalars;

final readonly class Periods
{
    public function __construct(
        public ?int $first,
        public ?int $second,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            first: Scalars::toIntOrNull($data['first'] ?? null),
            second: Scalars::toIntOrNull($data['second'] ?? null),
        );
    }
}
