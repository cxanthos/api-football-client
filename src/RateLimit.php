<?php

declare(strict_types=1);

namespace ApiFootball;

use Psr\Http\Message\ResponseInterface;

/**
 * Parsed rate-limit headers, present on every API-Football response (docs/design/sdk-design.md §1).
 * Any of these can be null if the server didn't send that particular header — the SDK never assumes
 * their presence.
 */
final readonly class RateLimit
{
    public function __construct(
        public ?int $dailyLimit,
        public ?int $dailyRemaining,
        public ?int $perMinuteLimit,
        public ?int $perMinuteRemaining,
    ) {}

    public static function fromHeaders(ResponseInterface $response): self
    {
        return new self(
            dailyLimit: self::header($response, 'x-ratelimit-requests-limit'),
            dailyRemaining: self::header($response, 'x-ratelimit-requests-remaining'),
            perMinuteLimit: self::header($response, 'X-RateLimit-Limit'),
            perMinuteRemaining: self::header($response, 'X-RateLimit-Remaining'),
        );
    }

    private static function header(ResponseInterface $response, string $name): ?int
    {
        $values = $response->getHeader($name);

        if ($values === []) {
            return null;
        }

        return (int) $values[0];
    }
}
