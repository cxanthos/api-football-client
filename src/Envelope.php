<?php

declare(strict_types=1);

namespace ApiFootball;

use ApiFootball\Internal\Scalars;
use JsonException;

/**
 * Decodes the `{get, parameters, errors, results, paging, response}` envelope every API-Football endpoint
 * returns. Two fields need real normalization, not just a straight decode — see docs/design/sdk-design.md
 * §1.1:
 *
 * - `parameters` is documented as an array but real responses send a flat object (`{"id": "39"}`).
 * - `errors` comes back as `[]` on success, a flat object on validation problems, or a bug-report object
 *   (`{time, bug, report}`) on server-side hiccups.
 *
 * Both are normalized here to `array<string,string>` so no resource ever has to deal with the raw shape.
 */
final readonly class Envelope
{
    /**
     * @param array<string,string> $parameters
     * @param array<string,string> $errors
     */
    private function __construct(
        public string $endpoint,
        public array $parameters,
        public array $errors,
        public int $results,
        public ?Paging $paging,
        public mixed $response,
        public ?ErrorId $errorId,
    ) {}

    /**
     * @throws JsonException if the body isn't valid JSON — that's a transport-layer concern, not an
     *     API-level one, so it's allowed to throw rather than degrade gracefully.
     */
    public static function fromJson(string $json, ?ErrorId $errorId = null): self
    {
        /** @var array<string,mixed> $decoded */
        $decoded = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);

        $paging = null;
        if (isset($decoded['paging']) && is_array($decoded['paging'])) {
            $paging = new Paging(
                current: Scalars::toInt($decoded['paging']['current'] ?? 1),
                total: Scalars::toInt($decoded['paging']['total'] ?? 1),
            );
        }

        return new self(
            endpoint: Scalars::toString($decoded['get'] ?? ''),
            parameters: self::normalizeStringMap($decoded['parameters'] ?? []),
            errors: self::normalizeStringMap($decoded['errors'] ?? []),
            results: Scalars::toInt($decoded['results'] ?? 0),
            paging: $paging,
            response: $decoded['response'] ?? null,
            errorId: $errorId,
        );
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array<string,string>
     */
    private static function normalizeStringMap(mixed $raw): array
    {
        if (!is_array($raw) || $raw === []) {
            return [];
        }

        foreach ($raw as $value) {
            if (!is_scalar($value)) {
                // Shape we haven't seen in the live spec's own examples — degrade rather than throw,
                // decoding must never break on an unrecognized payload (§1.1).
                return ['_raw' => json_encode($raw, JSON_THROW_ON_ERROR)];
            }
        }

        /** @var array<string,string> */
        return array_map(static fn(mixed $value): string => Scalars::toString($value), $raw);
    }
}
