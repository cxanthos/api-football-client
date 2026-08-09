<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\Coverage;
use ApiFootball\DTO\League;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;

/**
 * `GET /leagues`, `GET /leagues/seasons` — reference/bootstrap data. `coverage()` is not a distinct API
 * endpoint: it re-fetches `/leagues` filtered to one id+season and reads that season's `coverage` block
 * (docs/design/sdk-design.md §4.5). It is a plain, explicit call like any other resource method — nothing
 * else in this SDK calls it automatically.
 */
final readonly class Leagues extends AbstractResource
{
    /**
     * @return Result<list<League>>
     */
    public function list(
        ?int $id = null,
        ?string $name = null,
        ?string $country = null,
        ?string $code = null,
        ?int $season = null,
        ?int $team = null,
        ?string $type = null,
        ?bool $current = null,
        ?string $search = null,
        ?int $last = null,
    ): Result {
        $query = array_filter([
            'id' => $id,
            'name' => $name,
            'country' => $country,
            'code' => $code,
            'season' => $season,
            'team' => $team,
            'type' => $type,
            'current' => $current === null ? null : ($current ? 'true' : 'false'),
            'search' => $search,
            'last' => $last,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/leagues', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): League => League::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }

    /**
     * @return Result<list<int>>
     */
    public function seasons(): Result
    {
        $envelope = $this->transport->get('/leagues/seasons');

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $year): int => Scalars::toInt($year),
            $items,
        )));
    }

    /**
     * @return Result<Coverage>
     */
    public function coverage(int $id, int $season): Result
    {
        $envelope = $this->transport->get('/leagues', ['id' => $id, 'season' => $season]);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors);
        }

        $items = Scalars::toArray($envelope->response);
        $first = Scalars::toMap($items[0] ?? null);

        if ($first === []) {
            return Result::err(['league' => "No league found for id={$id}, season={$season}."]);
        }

        $seasons = Scalars::toArray($first['seasons'] ?? null);

        foreach ($seasons as $seasonData) {
            $seasonMap = Scalars::toMap($seasonData);

            if (Scalars::toInt($seasonMap['year'] ?? null) === $season) {
                return Result::ok(Coverage::fromArray(Scalars::toMap($seasonMap['coverage'] ?? null)));
            }
        }

        return Result::err(['season' => "Season {$season} not found for league {$id}."]);
    }
}
