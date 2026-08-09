<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Lineup;

use ApiFootball\Internal\Scalars;

final readonly class LineupCoach
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $photo,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toIntOrNull($data['id'] ?? null),
            name: Scalars::toStringOrNull($data['name'] ?? null),
            photo: Scalars::toStringOrNull($data['photo'] ?? null),
        );
    }
}
