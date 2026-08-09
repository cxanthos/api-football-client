<?php

declare(strict_types=1);

namespace ApiFootball\Resource;

use ApiFootball\Http\Transport;

/**
 * Shared plumbing for every resource (Countries today; Leagues, Teams, Fixtures, ... in later passes —
 * see docs/design/endpoint-catalog.md for the full MVP list). A resource method's job is always the same
 * shape: call $this->transport->get(), check the envelope for errors, map `response` into typed DTOs on
 * success.
 */
abstract readonly class AbstractResource
{
    public function __construct(
        protected Transport $transport,
    ) {}
}
