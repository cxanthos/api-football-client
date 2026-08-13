<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\Transfer;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;

/**
 * `GET /transfers`. No parameter is documented as required, so none is enforced client-side
 * (docs/design/sdk-design.md §4.2).
 */
final readonly class Transfers extends AbstractResource
{
    /**
     * @return Result<list<Transfer>>
     */
    public function list(?int $player = null, ?int $team = null): Result
    {
        $query = array_filter([
            'player' => $player,
            'team' => $team,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/transfers', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): Transfer => Transfer::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }
}
