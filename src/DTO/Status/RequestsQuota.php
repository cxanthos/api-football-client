<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Status;

use ApiFootball\Internal\Scalars;

final readonly class RequestsQuota
{
    public function __construct(
        public ?int $current,
        public ?int $limitDay,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            current: Scalars::toIntOrNull($data['current'] ?? null),
            limitDay: Scalars::toIntOrNull($data['limit_day'] ?? null),
        );
    }
}
