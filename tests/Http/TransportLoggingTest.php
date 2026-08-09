<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Http;

use ApiFootball\Config;
use ApiFootball\Exception\TransportException;
use ApiFootball\Http\Transport;
use ApiFootball\Internal\Scalars;
use ApiFootball\Tests\RecordingLogger;
use Exception;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LogLevel;

/**
 * Logging is wired once, centrally, in Http\Transport — this exercises Transport directly rather than
 * through a resource, since every resource shares the exact same logging behavior for free.
 */
final class TransportLoggingTest extends TestCase
{
    private const string SECRET_API_KEY = 'super-secret-key-do-not-log-me';

    public function testLogsTheRequestAndSuccessfulResponseAtDebugLevel(): void
    {
        [$transport, $logger, $mock] = $this->buildTransport();
        $mock->addResponse(new Response(200, [], $this->envelopeJson()));

        $transport->get('/countries', ['name' => 'england']);

        $debug = $logger->recordsAtLevel(LogLevel::DEBUG);
        self::assertCount(2, $debug);
        self::assertSame('API-Football request', $debug[0]['message']);
        self::assertStringContainsString('/countries', Scalars::toString($debug[0]['context']['uri']));
        self::assertSame('API-Football response', $debug[1]['message']);
        self::assertSame('countries', $debug[1]['context']['endpoint']);
    }

    public function testNeverLogsTheApiKeyAnywhere(): void
    {
        [$transport, $logger, $mock] = $this->buildTransport();
        $mock->addResponse(new Response(200, [], $this->envelopeJson(['errors' => ['x' => 'y']])));

        $transport->get('/countries');

        foreach ($logger->records as $record) {
            self::assertStringNotContainsString(self::SECRET_API_KEY, $record['message']);
            self::assertStringNotContainsString(self::SECRET_API_KEY, (string) json_encode($record['context']));
        }
    }

    public function testLogsApiLevelErrorsAtWarningLevelInsteadOfDebug(): void
    {
        [$transport, $logger, $mock] = $this->buildTransport();
        $mock->addResponse(new Response(200, [], $this->envelopeJson(['errors' => ['season' => 'bad']])));

        $transport->get('/countries');

        $warnings = $logger->recordsAtLevel(LogLevel::WARNING);
        self::assertCount(1, $warnings);
        self::assertSame('API-Football returned API-level errors', $warnings[0]['message']);
        self::assertSame(['season' => 'bad'], $warnings[0]['context']['errors']);

        // The outgoing request still logs at debug regardless — only the *response* debug log is skipped
        // in favor of the warning above.
        $debug = $logger->recordsAtLevel(LogLevel::DEBUG);
        self::assertCount(1, $debug);
        self::assertSame('API-Football request', $debug[0]['message']);
    }

    public function testLogsTransportFailuresAtErrorLevelBeforeThrowing(): void
    {
        [$transport, $logger, $mock] = $this->buildTransport();
        $mock->addException(new class extends Exception implements ClientExceptionInterface {});

        try {
            $transport->get('/countries');
            self::fail('Expected TransportException to be thrown.');
        } catch (TransportException) {
            // expected
        }

        $errors = $logger->recordsAtLevel(LogLevel::ERROR);
        self::assertCount(1, $errors);
        self::assertSame('API-Football transport failure', $errors[0]['message']);
    }

    public function testWarnsWhenDailyQuotaIsRunningLow(): void
    {
        [$transport, $logger, $mock] = $this->buildTransport();
        $mock->addResponse(new Response(200, [
            'x-ratelimit-requests-limit' => '100',
            'x-ratelimit-requests-remaining' => '3',
        ], $this->envelopeJson()));

        $transport->get('/countries');

        $warnings = $logger->recordsAtLevel(LogLevel::WARNING);
        self::assertCount(1, $warnings);
        self::assertSame('API-Football daily quota running low', $warnings[0]['message']);
        self::assertSame(3, $warnings[0]['context']['dailyRemaining']);
    }

    public function testWarnsWhenPerMinuteLimitIsExhausted(): void
    {
        [$transport, $logger, $mock] = $this->buildTransport();
        $mock->addResponse(new Response(200, [
            'X-RateLimit-Limit' => '30',
            'X-RateLimit-Remaining' => '0',
        ], $this->envelopeJson()));

        $transport->get('/countries');

        $warnings = $logger->recordsAtLevel(LogLevel::WARNING);
        self::assertCount(1, $warnings);
        self::assertSame('API-Football per-minute rate limit exhausted', $warnings[0]['message']);
    }

    public function testNoLoggerMeansNoLoggingAndNoErrors(): void
    {
        $mock = new MockClient();
        $psr17 = new Psr17Factory();
        $mock->addResponse(new Response(200, [], $this->envelopeJson()));

        $transport = new Transport(new Config(
            apiKey: self::SECRET_API_KEY,
            httpClient: $mock,
            requestFactory: $psr17,
            uriFactory: $psr17,
        ));

        $envelope = $transport->get('/countries');

        self::assertSame('countries', $envelope->endpoint);
    }

    /**
     * @return array{0: Transport, 1: RecordingLogger, 2: MockClient}
     */
    private function buildTransport(): array
    {
        $mock = new MockClient();
        $psr17 = new Psr17Factory();
        $logger = new RecordingLogger();

        $transport = new Transport(new Config(
            apiKey: self::SECRET_API_KEY,
            httpClient: $mock,
            requestFactory: $psr17,
            uriFactory: $psr17,
            logger: $logger,
        ));

        return [$transport, $logger, $mock];
    }

    /**
     * @param array<string,mixed> $overrides
     */
    private function envelopeJson(array $overrides = []): string
    {
        $envelope = array_merge([
            'get' => 'countries',
            'parameters' => [],
            'errors' => [],
            'results' => 0,
            'response' => [],
        ], $overrides);

        return (string) json_encode($envelope, JSON_THROW_ON_ERROR);
    }
}
