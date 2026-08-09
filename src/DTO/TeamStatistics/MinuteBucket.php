<?php

declare(strict_types=1);

namespace ApiFootball\DTO\TeamStatistics;

use ApiFootball\Internal\Scalars;

/**
 * One entry in a `{"0-15": {...}, "16-30": {...}, ...}` minute-range breakdown (goals-for/against,
 * yellow/red cards). The minute-range keys are dynamic, so they stay plain string array keys rather than
 * named properties — the bucket's own two fields are fully typed.
 */
final readonly class MinuteBucket
{
    public function __construct(
        public ?int $total,
        public ?string $percentage,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: Scalars::toIntOrNull($data['total'] ?? null),
            percentage: Scalars::toStringOrNull($data['percentage'] ?? null),
        );
    }

    /**
     * @param array<string,mixed> $data keyed by minute range
     * @return array<string,self>
     */
    public static function mapFromArray(array $data): array
    {
        $map = [];

        foreach ($data as $range => $bucket) {
            $map[Scalars::toString($range)] = self::fromArray(Scalars::toMap($bucket));
        }

        return $map;
    }
}
