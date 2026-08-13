<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\Venue;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;

/**
 * `GET /venues` (Tier 2). No parameter is documented as required, so none is enforced client-side
 * (docs/design/sdk-design.md §4.2). See `DTO\Venue` for why this reuses the same DTO as `/teams`'
 * embedded venue rather than a separate one.
 */
final readonly class Venues extends AbstractResource
{
    /**
     * @return Result<list<Venue>>
     */
    public function list(
        ?int $id = null,
        ?string $name = null,
        ?string $city = null,
        ?string $country = null,
        ?string $search = null,
    ): Result {
        $query = array_filter([
            'id' => $id,
            'name' => $name,
            'city' => $city,
            'country' => $country,
            'search' => $search,
        ], static fn(mixed $value): bool => $value !== null);

        $envelope = $this->transport->get('/venues', $query);

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors, $envelope->errorId);
        }

        $items = Scalars::toArray($envelope->response);

        return Result::ok(array_values(array_map(
            static fn(mixed $item): Venue => Venue::fromArray(Scalars::toMap($item)),
            $items,
        )));
    }
}
