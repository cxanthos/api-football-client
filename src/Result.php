<?php

declare(strict_types=1);

namespace ApiFootball;

use LogicException;

/**
 * Distinguishes transport/HTTP failures (which throw, see Exception\TransportException) from API-level
 * failures — HTTP 200/204 with a populated `errors` field. The latter is a normal, expected outcome of a
 * well-formed call (bad parameter combo, unknown id, etc.), not an exceptional one, so it's represented as
 * a value the caller must check rather than an exception the caller must catch.
 *
 * PHP has no runtime generics: the `@template T` below only gives static-analysis-time safety (PHPStan),
 * not a runtime guarantee. That's a known, accepted tradeoff (see docs/design/sdk-design.md §4.4), not an
 * oversight.
 *
 * @template T
 */
final readonly class Result
{
    /**
     * @param array<string,string> $errors
     */
    private function __construct(
        private bool $ok,
        private mixed $value,
        private array $errors,
    ) {}

    /**
     * @template U
     * @param U $value
     * @return self<U>
     */
    public static function ok(mixed $value): self
    {
        /** @var self<U> */
        return new self(true, $value, []);
    }

    /**
     * @param array<string,string> $errors
     * @return self<never>
     */
    public static function err(array $errors): self
    {
        /** @var self<never> */
        return new self(false, null, $errors);
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    /**
     * @return T
     */
    public function unwrap(): mixed
    {
        if (!$this->ok) {
            throw new LogicException(
                'Cannot unwrap an error Result: ' . implode('; ', $this->errors),
            );
        }

        /** @var T */
        return $this->value;
    }

    /**
     * @return array<string,string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
