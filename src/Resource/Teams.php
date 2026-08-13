<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\Country;
use ApiFootball\DTO\Team;
use ApiFootball\DTO\TeamStatistics;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;

/**
 * `GET /teams`, `GET /teams/statistics`, `GET /teams/seasons`, `GET /teams/countries`. See
 * docs/design/endpoint-catalog.md. The latter two are Tier 2 (sync/lookup helpers, no direct trivia
 * value) — included for completeness once you're already reaching for the `Teams` resource.
 */
final readonly class Teams extends AbstractResource
{
    /**
     * @return Result<list<Team>>
     */
    public function list(
        ?int $id = null,
        ?string $name = null,
        ?int $league = null,
        ?int $season = null,
        ?string $country = null,
        ?string $code = null,
        ?int $venue = null,
        ?string $search = null,
    ): Result {
        $query = array_filter([
            'id' => $id,
            'name' => $name,
            'league' => $league,
            'season' => $season,
            'country' => $country,
            'code' => $code,
            'venue' => $venue,
            'search' => $search,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/teams', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): Team => Team::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }

    /**
     * `league`, `season`, and `team` are all `required: true` in the live spec — this is one of the two
     * MVP endpoints (alongside `/players/squads`) where a client-side guard is backed by real evidence,
     * not invented (docs/design/sdk-design.md §4.2).
     *
     * @return Result<TeamStatistics>
     */
    public function statistics(int $league, int $season, int $team, ?string $date = null): Result
    {
        $query = array_filter([
            'league' => $league,
            'season' => $season,
            'team' => $team,
            'date' => $date,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/teams/statistics', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        return Result::ok(TeamStatistics::fromArray(Scalars::toMap($envelope->response)));
    }

    /**
     * @return Result<list<int>>
     */
    public function seasons(int $team): Result
    {
        $envelope = $this->transport->get('/teams/seasons', ['team' => $team]);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $year): int => Scalars::toInt($year),
            $items,
        )));
    }

    /**
     * Same `{name, code, flag}` shape as `GET /countries` itself, so it reuses the `Country` DTO rather
     * than duplicating it.
     *
     * @return Result<list<Country>>
     */
    public function countries(): Result
    {
        $envelope = $this->transport->get('/teams/countries');

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): Country => Country::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }
}
