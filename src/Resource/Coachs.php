<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\Coach;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;

/**
 * `GET /coachs`. Resource class is named `Coachs`, matching the API path exactly — consistent with every
 * other resource's 1:1 naming rule (docs/design/sdk-design.md §4.1); no readability rewrite to "Coaches".
 */
final readonly class Coachs extends AbstractResource
{
    /**
     * @return Result<list<Coach>>
     */
    public function list(?int $id = null, ?int $team = null, ?string $search = null): Result
    {
        $query = array_filter([
            'id' => $id,
            'team' => $team,
            'search' => $search,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/coachs', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): Coach => Coach::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }
}
