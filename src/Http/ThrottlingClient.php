<?php

declare(strict_types=1);

namespace ApiFootball\Http;

use ApiFootball\RateLimit;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Opt-in PSR-18 decorator that respects the per-minute rate limit and optionally slows down further once
 * the daily quota is running low (docs/design/sdk-design.md §4.6). Not wired in anywhere by default — wrap
 * your own PSR-18 client with it and pass the result as `Client`'s `$httpClient`:
 *
 *   $client = new ApiFootball\Client(
 *       apiKey: '...',
 *       httpClient: new ApiFootball\Http\ThrottlingClient(Http\Discovery\Psr18ClientDiscovery::find()),
 *   );
 *
 * Only ever delays *before* sending a request. It never inspects a response to decide whether to retry —
 * a `429` (or any other status) passes straight through unchanged. Blind-retrying 429 is explicitly
 * out of scope for this SDK under any configuration.
 *
 * The per-minute limit isn't hardcoded (NFR-12: plan-agnostic) — it starts unknown and is learned from the
 * `X-RateLimit-Limit` header the first time it's seen, so the very first call (or every call, if you never
 * inspect headers yourself) goes through unthrottled until the API has actually told this client what its
 * limit is.
 */
final class ThrottlingClient implements ClientInterface
{
    /** @var list<float> microtime() timestamps of requests sent within the current 60s window */
    private array $requestTimestamps = [];

    private ?int $perMinuteLimit = null;

    public function __construct(
        private readonly ClientInterface $inner,
        private readonly int $lowDailyRemainingThreshold = 50,
        private readonly float $lowDailyRemainingDelaySeconds = 1.0,
        private readonly Sleeper $sleeper = new SystemSleeper(),
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->waitForPerMinuteWindow();

        $response = $this->inner->sendRequest($request);

        $this->requestTimestamps[] = microtime(true);

        $rateLimit = RateLimit::fromHeaders($response);

        if ($rateLimit->perMinuteLimit !== null) {
            $this->perMinuteLimit = $rateLimit->perMinuteLimit;
        }

        if ($rateLimit->dailyRemaining !== null && $rateLimit->dailyRemaining <= $this->lowDailyRemainingThreshold) {
            $this->sleeper->sleep($this->lowDailyRemainingDelaySeconds);
        }

        return $response;
    }

    private function waitForPerMinuteWindow(): void
    {
        if ($this->perMinuteLimit === null) {
            return;
        }

        $now = microtime(true);

        $this->requestTimestamps = array_values(array_filter(
            $this->requestTimestamps,
            static fn(float $timestamp): bool => $timestamp > $now - 60.0,
        ));

        if (count($this->requestTimestamps) < $this->perMinuteLimit) {
            return;
        }

        $oldest = $this->requestTimestamps[0];
        $waitSeconds = 60.0 - ($now - $oldest);

        if ($waitSeconds > 0.0) {
            $this->sleeper->sleep($waitSeconds);
        }
    }
}
