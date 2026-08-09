<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Http;

use ApiFootball\Http\Sleeper;

/** Records requested sleep durations instead of actually blocking — keeps throttle tests instant. */
final class FakeSleeper implements Sleeper
{
    /** @var list<float> */
    public array $calls = [];

    public function sleep(float $seconds): void
    {
        $this->calls[] = $seconds;
    }
}
