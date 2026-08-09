<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * `GET /leagues` response item. The `country` block is exactly the same `{name, code, flag}` shape as
 * `GET /countries`'s own response, so it reuses the `Country` DTO rather than duplicating it.
 */
final readonly class League
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public ?string $logo,
        public Country $country,
        /** @var list<LeagueSeason> */
        public array $seasons,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $league = Scalars::toMap($data['league'] ?? null);
        $seasons = Scalars::toArray($data['seasons'] ?? null);

        return new self(
            id: Scalars::toInt($league['id'] ?? null),
            name: Scalars::toString($league['name'] ?? null),
            type: Scalars::toString($league['type'] ?? null),
            logo: Scalars::toStringOrNull($league['logo'] ?? null),
            country: Country::fromArray(Scalars::toMap($data['country'] ?? null)),
            seasons: array_values(array_map(
                static fn(mixed $season): LeagueSeason => LeagueSeason::fromArray(Scalars::toMap($season)),
                $seasons,
            )),
        );
    }
}
