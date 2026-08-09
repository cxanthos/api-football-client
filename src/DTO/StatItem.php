<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * One entry in `GET /fixtures/statistics`' `statistics[]` — e.g. `{type: "Ball Possession", value: "32%"}`
 * or `{type: "Total Shots", value: 9}`. `value` is faithfully kept as whatever scalar shape the API sent
 * (int for counts, string for formatted percentages, null when not tracked) rather than coerced to one type.
 */
final readonly class StatItem
{
    public function __construct(
        public string $type,
        public int|string|null $value,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $rawValue = $data['value'] ?? null;

        return new self(
            type: Scalars::toString($data['type'] ?? null),
            value: match (true) {
                is_int($rawValue) => $rawValue,
                is_string($rawValue) => $rawValue,
                default => null,
            },
        );
    }
}
