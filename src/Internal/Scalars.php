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

    public static function toIntOrNull(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? (int) $value : null;
    }

    public static function toBool(mixed $value): bool
    {
        return (bool) $value;
    }

    public static function toBoolOrNull(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        return (bool) $value;
    }

    /**
     * For narrowing a JSON *list* before iterating it (e.g. with array_map). Key type is left generic —
     * use {@see toMap()} instead when the value represents a JSON *object* you're about to index by
     * string key.
     *
     * @return array<int|string, mixed>
     */
    public static function toArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * For narrowing a JSON *object* before indexing it by string key (every DTO's ::fromArray() input).
     * Any non-string keys are dropped defensively — json_decode(..., associative: true) never produces
     * one for a genuine JSON object, so in practice this is just a type-narrowing no-op.
     *
     * @return array<string, mixed>
     */
    public static function toMap(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $map = [];

        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $map[$key] = $item;
            }
        }

        return $map;
    }
}
