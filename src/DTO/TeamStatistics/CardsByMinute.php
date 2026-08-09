<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

final readonly class CardsByMinute
{
    /**
     * @param array<string,MinuteBucket> $yellow
     * @param array<string,MinuteBucket> $red
     */
    public function __construct(
        public array $yellow,
        public array $red,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            yellow: MinuteBucket::mapFromArray(Scalars::toMap($data['yellow'] ?? null)),
            red: MinuteBucket::mapFromArray(Scalars::toMap($data['red'] ?? null)),
        );
    }
}
