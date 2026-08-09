<?php

declare(strict_types=1);

namespace ApiFootball\DTO\Lineup;

use ApiFootball\Internal\Scalars;

final readonly class TeamColors
{
    public function __construct(
        public Colors $player,
        public Colors $goalkeeper,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            player: Colors::fromArray(Scalars::toMap($data['player'] ?? null)),
            goalkeeper: Colors::fromArray(Scalars::toMap($data['goalkeeper'] ?? null)),
        );
    }
}
