<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Lineup;

use ApiFootball\Internal\Scalars;

/** `grid` is a pitch-position coordinate like `"1:1"` — kept as a raw string, not parsed. */
final readonly class LineupPlayer
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?int $number,
        public ?string $pos,
        public ?string $grid,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Scalars::toIntOrNull($data['id'] ?? null),
            name: Scalars::toStringOrNull($data['name'] ?? null),
            number: Scalars::toIntOrNull($data['number'] ?? null),
            pos: Scalars::toStringOrNull($data['pos'] ?? null),
            grid: Scalars::toStringOrNull($data['grid'] ?? null),
        );
    }
}
