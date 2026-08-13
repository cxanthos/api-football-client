<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\Fixture;
use ApiFootball\DTO\FixtureEvent;
use ApiFootball\DTO\FixtureTeamPlayers;
use ApiFootball\DTO\FixtureTeamStatistics;
use ApiFootball\DTO\Lineup;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;

/**
 * `GET /fixtures`, `GET /fixtures/events`, `GET /fixtures/headtohead` (MVP), plus `GET /fixtures/rounds`,
 * `GET /fixtures/lineups`, `GET /fixtures/statistics`, `GET /fixtures/players` (Tier 2). See
 * docs/design/endpoint-catalog.md.
 */
final readonly class Fixtures extends AbstractResource
{
    /**
     * No parameter is schema-required or docs-required, so none is enforced client-side (see
     * docs/design/sdk-design.md §4.2) — an earlier draft of this SDK invented an "at least one filter"
     * guard here with no evidence behind it; that guard was removed.
     *
     * @return Result<list<Fixture>>
     */
    public function list(
        ?int $id = null,
        ?string $ids = null,
        ?string $live = null,
        ?string $date = null,
        ?int $league = null,
        ?int $season = null,
        ?int $team = null,
        ?int $last = null,
        ?int $next = null,
        ?string $from = null,
        ?string $to = null,
        ?string $round = null,
        ?string $status = null,
        ?int $venue = null,
        ?string $timezone = null,
    ): Result {
        $query = array_filter([
            'id' => $id,
            'ids' => $ids,
            'live' => $live,
            'date' => $date,
            'league' => $league,
            'season' => $season,
            'team' => $team,
            'last' => $last,
            'next' => $next,
            'from' => $from,
            'to' => $to,
            'round' => $round,
            'status' => $status,
            'venue' => $venue,
            'timezone' => $timezone,
        ], static fn(mixed $value): bool => $value !== null);

        return $this->fetchFixtureList('/fixtures', $query);
    }

    /**
     * @return Result<list<FixtureEvent>>
     */
    public function events(int $fixture, ?int $team = null, ?int $player = null, ?string $type = null): Result
    {
        $query = array_filter([
            'fixture' => $fixture,
            'team' => $team,
            'player' => $player,
            'type' => $type,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/fixtures/events', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): FixtureEvent => FixtureEvent::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }

    /**
     * @param string $h2h Two team ids joined by a dash, e.g. `'33-34'`.
     *
     * @return Result<list<Fixture>>
     */
    public function headToHead(
        string $h2h,
        ?string $date = null,
        ?int $league = null,
        ?int $season = null,
        ?int $last = null,
        ?int $next = null,
        ?string $from = null,
        ?string $to = null,
        ?string $status = null,
        ?int $venue = null,
        ?string $timezone = null,
    ): Result {
        $query = array_filter([
            'h2h' => $h2h,
            'date' => $date,
            'league' => $league,
            'season' => $season,
            'last' => $last,
            'next' => $next,
            'from' => $from,
            'to' => $to,
            'status' => $status,
            'venue' => $venue,
            'timezone' => $timezone,
        ], static fn(mixed $value): bool => $value !== null);

        return $this->fetchFixtureList('/fixtures/headtohead', $query);
    }

    /**
     * Verified against the live spec's example for the default case only (no `dates` param) — the response
     * is a flat `list<string>` of round names. Passing `dates: true` is documented to change the response
     * shape (adds each round's date); that variant hasn't been observed, so this method doesn't attempt to
     * model it — the raw envelope response would need decoding by hand in that case.
     *
     * @return Result<list<string>>
     */
    public function rounds(
        int $league,
        int $season,
        ?bool $current = null,
        ?bool $dates = null,
        ?string $timezone = null,
    ): Result {
        $query = array_filter([
            'league' => $league,
            'season' => $season,
            'current' => $current === null ? null : ($current ? 'true' : 'false'),
            'dates' => $dates === null ? null : ($dates ? 'true' : 'false'),
            'timezone' => $timezone,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/fixtures/rounds', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): string => Scalars::toString($item),
            $items,
        )));
    }

    /**
     * @return Result<list<Lineup>>
     */
    public function lineups(
        int $fixture,
        ?int $team = null,
        ?int $player = null,
        ?string $type = null,
    ): Result {
        $query = array_filter([
            'fixture' => $fixture,
            'team' => $team,
            'player' => $player,
            'type' => $type,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/fixtures/lineups', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): Lineup => Lineup::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }

    /**
     * `half: true` adds halftime statistics — per the live spec, only for 2024+ seasons.
     *
     * @return Result<list<FixtureTeamStatistics>>
     */
    public function statistics(
        int $fixture,
        ?int $team = null,
        ?string $type = null,
        ?bool $half = null,
    ): Result {
        $query = array_filter([
            'fixture' => $fixture,
            'team' => $team,
            'type' => $type,
            'half' => $half === null ? null : ($half ? 'true' : 'false'),
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/fixtures/statistics', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): FixtureTeamStatistics => FixtureTeamStatistics::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }

    /**
     * The richest and most expensive per-match endpoint — full statistics for every player who featured.
     *
     * @return Result<list<FixtureTeamPlayers>>
     */
    public function players(int $fixture, ?int $team = null): Result
    {
        $query = array_filter([
            'fixture' => $fixture,
            'team' => $team,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/fixtures/players', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): FixtureTeamPlayers => FixtureTeamPlayers::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }

    /**
     * @param array<string,int|string> $query
     *
     * @return Result<list<Fixture>>
     */
    private function fetchFixtureList(string $path, array $query): Result
    {
        $envelope = $this->transport->get($path, $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): Fixture => Fixture::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }
}
