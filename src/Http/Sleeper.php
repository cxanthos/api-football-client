<?php

declare(strict_types=1);

namespace ApiFootball\Http;

/**
 * Abstraction over "wait N seconds" so `ThrottlingClient` is testable without a test suite that actually
 * sleeps for real seconds.
 */
interface Sleeper
{
    public function sleep(float $seconds): void;
}
