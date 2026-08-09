<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/** The minimal `{id, name}` player reference embedded in fixture events (`player`, `assist`). */
final readonly class PlayerRef
{
    public function __construct(
        public ?int $id,
        public ?string $name,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toIntOrNull($data['id'] ?? null),
            name: Scalars::toStringOrNull($data['name'] ?? null),
        );
    }
}
