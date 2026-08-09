<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\DTO\Status\AccountInfo;
use ApiFootball\DTO\Status\RequestsQuota;
use ApiFootball\DTO\Status\Subscription;
use ApiFootball\Internal\Scalars;

/**
 * `GET /status` response — account/quota info. Not in the OpenAPI spec at all; this shape is transcribed
 * from the docs guide page's own worked example (docs/design/endpoint-catalog.md).
 */
final readonly class Status
{
    public function __construct(
        public AccountInfo $account,
        public Subscription $subscription,
        public RequestsQuota $requests,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            account: AccountInfo::fromArray(Scalars::toMap($data['account'] ?? null)),
            subscription: Subscription::fromArray(Scalars::toMap($data['subscription'] ?? null)),
            requests: RequestsQuota::fromArray(Scalars::toMap($data['requests'] ?? null)),
        );
    }
}
