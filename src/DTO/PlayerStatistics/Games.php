<?php

declare(strict_types=1);

namespace ApiFootball\DTO\PlayerStatistics;

use ApiFootball\Internal\Scalars;

/** `rating` is sent as a formatted decimal string ("8.053333"), not a number — kept faithful, not parsed. */
final readonly class Games
{
    public function __construct(
        public ?int $appearences,
        public ?int $lineups,
        public ?int $minutes,
        public ?int $number,
        public ?string $position,
        public ?string $rating,
        public bool $captain,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            appearences: Scalars::toIntOrNull($data['appearences'] ?? null),
            lineups: Scalars::toIntOrNull($data['lineups'] ?? null),
            minutes: Scalars::toIntOrNull($data['minutes'] ?? null),
            number: Scalars::toIntOrNull($data['number'] ?? null),
            position: Scalars::toStringOrNull($data['position'] ?? null),
            rating: Scalars::toStringOrNull($data['rating'] ?? null),
            captain: Scalars::toBool($data['captain'] ?? false),
        );
    }
}
