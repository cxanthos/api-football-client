<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * `GET /countries` response item (docs/design/endpoint-catalog.md). `code` and `flag` are nullable —
 * pseudo-countries used for international competitions (e.g. "World") have neither.
 */
final readonly class Country
{
    public function __construct(
        public string $name,
        public ?string $code,
        public ?string $flag,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: Scalars::toString($data['name'] ?? null),
            code: Scalars::toStringOrNull($data['code'] ?? null),
            flag: Scalars::toStringOrNull($data['flag'] ?? null),
        );
    }
}
