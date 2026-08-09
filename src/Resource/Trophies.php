<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\Trophy;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;

/**
 * `GET /trophies`. No parameter is documented as required, so none is enforced client-side
 * (docs/design/sdk-design.md §4.2). `players`/`coachs` accept a dash-joined id list (e.g. `'1-2-3'`).
 */
final readonly class Trophies extends AbstractResource
{
    /**
     * @return Result<list<Trophy>>
     */
    public function list(
        ?int $player = null,
        ?string $players = null,
        ?int $coach = null,
        ?string $coachs = null,
    ): Result {
        $query = array_filter([
            'player' => $player,
            'players' => $players,
            'coach' => $coach,
            'coachs' => $coachs,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/trophies', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): Trophy => Trophy::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }
}
