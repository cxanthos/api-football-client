<?php

declare(strict_types=1);

namespace ApiFootball\Tests\Resource;

use ApiFootball\Exception\TransportException;
use ApiFootball\Tests\ResourceTestCase;

final class AccountTest extends ResourceTestCase
{
    public function testStatusReturnsMappedAccountInfoOnSuccess(): void
    {
        // Shape lifted verbatim from the docs guide page's own worked example (not the OpenAPI spec —
        // this endpoint isn't in it at all).
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'status',
            'response' => [
                'account' => ['firstname' => 'xxxx', 'lastname' => 'XXXXXX', 'email' => 'xxx@xxx.com'],
                'subscription' => ['plan' => 'Free', 'end' => '2020-04-10T23:24:27+00:00', 'active' => true],
                'requests' => ['current' => 12, 'limit_day' => 100],
            ],
        ]));

        $result = $client->account()->status();

        self::assertTrue($result->isOk());
        $status = $result->unwrap();
        self::assertSame('Free', $status->subscription->plan);
        self::assertTrue($status->subscription->active);
        self::assertSame(12, $status->requests->current);
        self::assertSame(100, $status->requests->limitDay);
    }

    public function testStatusReturnsErrResultWhenApiReturnsErrorsOnHttp200(): void
    {
        $client = $this->clientWithResponse($this->envelopeJson([
            'get' => 'status',
            'errors' => ['key' => 'Invalid API key.'],
        ]));

        $result = $client->account()->status();

        self::assertFalse($result->isOk());
    }

    public function testStatusThrowsTransportExceptionOnHttpClientFailure(): void
    {
        $client = $this->clientWithTransportFailure();

        $this->expectException(TransportException::class);

        $client->account()->status();
    }
}
