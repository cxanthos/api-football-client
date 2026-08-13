<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\PlayerStatistics;
use ApiFootball\DTO\PlayerTeamSeasons;
use ApiFootball\DTO\ProfiledPlayer;
use ApiFootball\DTO\Squad;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;
use InvalidArgumentException;

/**
 * `GET /players`, `GET /players/squads`, `GET /players/topscorers`, `GET /players/topassists`,
 * `GET /players/topyellowcards`, `GET /players/topredcards` (MVP), plus `GET /players/profiles`,
 * `GET /players/seasons`, `GET /players/teams` (Tier 2). See docs/design/endpoint-catalog.md.
 */
final readonly class Players extends AbstractResource
{
    /**
     * Paginated, 20 results/page, via `$page`. No combination of `id`/`team`+`season`/`league`+`season` is
     * enforced client-side — the docs describe those as the sane combos but never say "required", so per
     * §4.2 the SDK doesn't invent a guard. Low-level only: no pagination iterator, no
     * `allPlayersFor*()` helper.
     *
     * @return Result<list<PlayerStatistics>>
     */
    public function statistics(
        ?int $id = null,
        ?int $team = null,
        ?int $league = null,
        ?int $season = null,
        ?string $search = null,
        ?int $page = null,
    ): Result {
        $query = array_filter([
            'id' => $id,
            'team' => $team,
            'league' => $league,
            'season' => $season,
            'search' => $search,
            'page' => $page,
        ], static fn(mixed $value): bool => $value !== null);

        return $this->fetchPlayerStatisticsList('/players', $query);
    }

    /**
     * @return Result<list<Squad>>
     */
    public function squads(?int $team = null, ?int $player = null): Result
    {
        if ($team === null && $player === null) {
            throw new InvalidArgumentException(
                'players()->squads() requires at least one of $team or $player — confirmed by the API '
                . 'docs text ("This endpoint requires at least one parameter"), not schema-enforced '
                . '(docs/design/sdk-design.md §4.2).',
            );
        }

        $query = array_filter([
            'team' => $team,
            'player' => $player,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/players/squads', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): Squad => Squad::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }

    /**
     * @return Result<list<PlayerStatistics>>
     */
    public function topScorers(int $league, int $season): Result
    {
        return $this->fetchPlayerStatisticsList('/players/topscorers', ['league' => $league, 'season' => $season]);
    }

    /**
     * @return Result<list<PlayerStatistics>>
     */
    public function topAssists(int $league, int $season): Result
    {
        return $this->fetchPlayerStatisticsList('/players/topassists', ['league' => $league, 'season' => $season]);
    }

    /**
     * Tie-break order per the live spec: most yellows, then most reds, then most assists, then fewest
     * minutes played.
     *
     * @return Result<list<PlayerStatistics>>
     */
    public function topYellowCards(int $league, int $season): Result
    {
        return $this->fetchPlayerStatisticsList('/players/topyellowcards', ['league' => $league, 'season' => $season]);
    }

    /**
     * Tie-break order per the live spec: most reds, then most yellows, then most assists, then fewest
     * minutes played.
     *
     * @return Result<list<PlayerStatistics>>
     */
    public function topRedCards(int $league, int $season): Result
    {
        return $this->fetchPlayerStatisticsList('/players/topredcards', ['league' => $league, 'season' => $season]);
    }

    /**
     * A genuinely different player shape from `statistics()`/`topScorers()` etc. — see `DTO\ProfiledPlayer`.
     * Paginated (`page`), same as `statistics()`.
     *
     * @return Result<list<ProfiledPlayer>>
     */
    public function profiles(?int $player = null, ?string $search = null, ?int $page = null): Result
    {
        $query = array_filter([
            'player' => $player,
            'search' => $search,
            'page' => $page,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/players/profiles', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): ProfiledPlayer => ProfiledPlayer::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }

    /**
     * @return Result<list<int>>
     */
    public function seasons(?int $player = null): Result
    {
        $query = array_filter([
            'player' => $player,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/players/seasons', $query);

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
     * Career path: every club the player has belonged to, with which seasons there.
     *
     * @return Result<list<PlayerTeamSeasons>>
     */
    public function teams(int $player): Result
    {
        $envelope = $this->transport->get('/players/teams', ['player' => $player]);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): PlayerTeamSeasons => PlayerTeamSeasons::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }

    /**
     * @param array<string,int|string> $query
     *
     * @return Result<list<PlayerStatistics>>
     */
    private function fetchPlayerStatisticsList(string $path, array $query): Result
    {
        $envelope = $this->transport->get($path, $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): PlayerStatistics => PlayerStatistics::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }
}
