<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Fixture;

use ApiFootball\Internal\Scalars;

final readonly class Teams
{
    public function __construct(
        public Participant $home,
        public Participant $away,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            home: Participant::fromArray(Scalars::toMap($data['home'] ?? null)),
            away: Participant::fromArray(Scalars::toMap($data['away'] ?? null)),
        );
    }
}
