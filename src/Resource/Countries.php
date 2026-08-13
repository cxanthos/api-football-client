<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\Country;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;

/**
 * `GET /countries` — reference data, powers filters/flags elsewhere (docs/design/endpoint-catalog.md).
 * No required parameters, and none are invented client-side — see docs/design/sdk-design.md §4.2 for why
 * the SDK never adds a requirement the API docs don't state.
 */
final readonly class Countries extends AbstractResource
{
    /**
     * @return Result<list<Country>>
     */
    public function list(?string $name = null, ?string $code = null, ?string $search = null): Result
    {
        $query = array_filter(
            ['name' => $name, 'code' => $code, 'search' => $search],
            static fn(?string $value): bool => $value !== null,
        );

        $envelope = $this->transport->get('/countries', $query);

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
