<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Status;

use ApiFootball\Internal\Scalars;

final readonly class AccountInfo
{
    public function __construct(
        public ?string $firstname,
        public ?string $lastname,
        public ?string $email,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            firstname: Scalars::toStringOrNull($data['firstname'] ?? null),
            lastname: Scalars::toStringOrNull($data['lastname'] ?? null),
            email: Scalars::toStringOrNull($data['email'] ?? null),
        );
    }
}
