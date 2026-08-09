<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\Standings as StandingsDto;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;

/**
 * `GET /standings`. See docs/design/endpoint-catalog.md. Schema only requires `season` — `league`/`team`
 * are optional. Docs say "standings for a league *or* a team", meaning `season` alone fetches every
 * league's standings for that season at once; that's schema-legal and deliberately left un-guarded rather
 * than blocked client-side (docs/design/sdk-design.md §7) — it's on the caller to pass `league` or `team`
 * when that's what they actually want.
 */
final readonly class Standings extends AbstractResource
{
    /**
     * @return Result<list<StandingsDto>>
     */
    public function list(int $season, ?int $league = null, ?int $team = null): Result
    {
        $query = array_filter([
            'league' => $league,
            'season' => $season,
            'team' => $team,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/standings', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): StandingsDto => StandingsDto::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }
}
