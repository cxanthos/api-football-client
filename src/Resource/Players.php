<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\PlayerStatistics;
use ApiFootball\DTO\Squad;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;
use InvalidArgumentException;

/**
 * `GET /players`, `GET /players/squads`, `GET /players/topscorers`, `GET /players/topassists`,
 * `GET /players/topyellowcards`, `GET /players/topredcards`. See docs/design/endpoint-catalog.md.
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
            return Result::err($envelope->errors);
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
     * @param array<string,int|string> $query
     *
     * @return Result<list<PlayerStatistics>>
     */
    private function fetchPlayerStatisticsList(string $path, array $query): Result
    {
        $envelope = $this->transport->get($path, $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): PlayerStatistics => PlayerStatistics::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }
}
