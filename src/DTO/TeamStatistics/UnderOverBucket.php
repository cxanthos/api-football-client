<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

/** One entry in a `{"0.5": {over,under}, "1.5": {...}, ...}` goals-threshold breakdown. */
final readonly class UnderOverBucket
{
    public function __construct(
        public ?int $over,
        public ?int $under,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            over: Scalars::toIntOrNull($data['over'] ?? null),
            under: Scalars::toIntOrNull($data['under'] ?? null),
        );
    }

    /**
     * @param array<string,mixed> $data keyed by goal threshold
     * @return array<string,self>
     */
    public static function mapFromArray(array $data): array
    {
        $map = [];

        foreach ($data as $threshold => $bucket) {
            $map[Scalars::toString($threshold)] = self::fromArray(Scalars::toMap($bucket));
        }

        return $map;
    }
}
