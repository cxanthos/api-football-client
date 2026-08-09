<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Status;

use ApiFootball\Internal\Scalars;

final readonly class Subscription
{
    public function __construct(
        public ?string $plan,
        public ?string $end,
        public bool $active,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            plan: Scalars::toStringOrNull($data['plan'] ?? null),
            end: Scalars::toStringOrNull($data['end'] ?? null),
            active: Scalars::toBool($data['active'] ?? false),
        );
    }
}
