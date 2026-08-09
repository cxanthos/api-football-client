<?php

declare(strict_types=1);

namespace ApiFootball\Exception;

use RuntimeException;

/**
 * Thrown for HTTP/network failures only (connection refused, DNS failure, timeout, malformed response
 * body, etc.) — never for API-level errors returned on a normal HTTP 200/204, which are represented as a
 * Result instead (docs/design/sdk-design.md §4.4).
 */
final class TransportException extends RuntimeException {}
