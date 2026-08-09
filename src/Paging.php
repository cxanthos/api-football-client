<?php

declare(strict_types=1);

namespace ApiFootball;

/**
 * The envelope's optional `paging` block. Not every endpoint returns one (docs/design/sdk-design.md §1 —
 * only `/players` and `/players/profiles` paginate in the MVP tier).
 */
final readonly class Paging
{
    public function __construct(
        public int $current,
        public int $total,
    ) {}
}
