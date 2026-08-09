<?php

declare(strict_types=1);

namespace ApiFootball\Http;

/** The real `Sleeper` — actually blocks the current process via `usleep()`. */
final class SystemSleeper implements Sleeper
{
    public function sleep(float $seconds): void
    {
        if ($seconds <= 0.0) {
            return;
        }

        usleep((int) ($seconds * 1_000_000));
    }
}
