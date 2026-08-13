<?php

declare(strict_types=1);

namespace ApiFootball;

/**
 * A deliberately narrow set of SDK-classified error categories, attached to a `Result` alongside the raw
 * `errors` map. Only given a case when the underlying signal is reliable enough to classify from — see
 * docs/design/sdk-design.md §1: this API sometimes returns HTTP 200 for auth/param errors, so status code
 * cannot be trusted to classify those. `RateLimited` is safe because 429 is the one status this API uses
 * consistently.
 *
 * Deliberately does *not* attempt to cover invalid params, auth failures, etc. — those aren't reliably
 * distinguishable from status code or from the free-text `errors` map without brittle message matching.
 * Use `Result::errors()` for those.
 */
enum ErrorId: string
{
    case RateLimited = 'rate_limited';
}
