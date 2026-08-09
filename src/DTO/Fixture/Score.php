<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Fixture;

use ApiFootball\DTO\HomeAwayPair;
use ApiFootball\Internal\Scalars;

final readonly class Score
{
    public function __construct(
        public HomeAwayPair $halftime,
        public HomeAwayPair $fulltime,
        public HomeAwayPair $extratime,
        public HomeAwayPair $penalty,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            halftime: HomeAwayPair::fromArray(Scalars::toMap($data['halftime'] ?? null)),
            fulltime: HomeAwayPair::fromArray(Scalars::toMap($data['fulltime'] ?? null)),
            extratime: HomeAwayPair::fromArray(Scalars::toMap($data['extratime'] ?? null)),
            penalty: HomeAwayPair::fromArray(Scalars::toMap($data['penalty'] ?? null)),
        );
    }
}
