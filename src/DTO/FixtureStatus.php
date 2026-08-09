<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

final readonly class FixtureStatus
{
    public function __construct(
        public ?FixtureStatusCode $code,
        public string $raw,
        public ?string $long,
        public ?int $elapsed,
        public ?int $extra,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $raw = Scalars::toString($data['short'] ?? null);

        return new self(
            code: FixtureStatusCode::tryFrom($raw),
            raw: $raw,
            long: Scalars::toStringOrNull($data['long'] ?? null),
            elapsed: Scalars::toIntOrNull($data['elapsed'] ?? null),
            extra: Scalars::toIntOrNull($data['extra'] ?? null),
        );
    }
}
