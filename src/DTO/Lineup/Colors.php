<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Lineup;

use ApiFootball\Internal\Scalars;

/** Hex color codes (no `#` prefix, per the API) for a kit — player or goalkeeper. */
final readonly class Colors
{
    public function __construct(
        public ?string $primary,
        public ?string $number,
        public ?string $border,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            primary: Scalars::toStringOrNull($data['primary'] ?? null),
            number: Scalars::toStringOrNull($data['number'] ?? null),
            border: Scalars::toStringOrNull($data['border'] ?? null),
        );
    }
}
