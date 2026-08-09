<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\DTO\Status;
use ApiFootball\Internal\Scalars;
use ApiFootball\Result;

/**
 * `GET /status` — not in the OpenAPI spec at all (docs/design/endpoint-catalog.md), and does not count
 * against the daily quota. `response` is a single object here, not a list — same shape as
 * `/teams/statistics`.
 */
final readonly class Account extends AbstractResource
{
    /**
     * @return Result<Status>
     */
    public function status(): Result
    {
        $envelope = $this->transport->get('/status');

        if ($envelope->hasErrors()) {
            return Result::err($envelope->errors);
        }

        return Result::ok(Status::fromArray(Scalars::toMap($envelope->response)));
    }
}
