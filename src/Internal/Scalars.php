<?php

declare(strict_types=1);

namespace ApiFootball\Internal;

/**
 * @internal Not part of the public API. Every DTO's ::fromArray() and Envelope::fromJson() need to narrow
 * a `mixed` JSON-decoded value before casting it — PHPStan (correctly) refuses a direct `(string) $mixed`
 * or `(int) $mixed` since an array/object could reach the cast and blow up at runtime. Centralized here
 * once instead of repeated ad hoc in every DTO.
 */
final class Scalars
{
    private function __construct() {}

    public static function toString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    public static function toStringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? (string) $value : null;
    }

    public static function toInt(mixed $value): int
    {
        return is_scalar($value) ? (int) $value : 0;
    }
}
