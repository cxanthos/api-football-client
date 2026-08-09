<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

/**
 * The ~19 fixture status short codes documented on `GET /fixtures` (docs/design/endpoint-catalog.md).
 * Deliberately consumed via `::tryFrom()` (see `FixtureStatus`), never `::from()` — the API adding a new
 * status code should degrade to "unrecognized, here's the raw string" rather than crash decoding, matching
 * the same never-throw-on-an-unrecognized-shape principle as `Envelope`'s error normalization (§1.1).
 */
enum FixtureStatusCode: string
{
    case TimeToBeDefined = 'TBD';
    case NotStarted = 'NS';
    case FirstHalf = '1H';
    case Halftime = 'HT';
    case SecondHalf = '2H';
    case ExtraTime = 'ET';
    case BreakTime = 'BT';
    case PenaltyInProgress = 'P';
    case Suspended = 'SUSP';
    case Interrupted = 'INT';
    case MatchFinished = 'FT';
    case MatchFinishedAfterExtraTime = 'AET';
    case MatchFinishedAfterPenalties = 'PEN';
    case Postponed = 'PST';
    case Cancelled = 'CANC';
    case Abandoned = 'ABD';
    case TechnicalLoss = 'AWD';
    case WalkOver = 'WO';
    case InProgress = 'LIVE';
}
