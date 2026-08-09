<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/**
 * `GET /coachs` response item. `team` is nullable — a coach between jobs may have none
 * (docs/design/endpoint-catalog.md).
 */
final readonly class Coach
{
    /**
     * @param list<CoachCareerEntry> $career
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $firstname,
        public ?string $lastname,
        public ?int $age,
        public Birth $birth,
        public ?string $nationality,
        public ?string $height,
        public ?string $weight,
        public ?string $photo,
        public ?TeamRef $team,
        public array $career,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $career = Scalars::toArray($data['career'] ?? null);
        $team = Scalars::toMap($data['team'] ?? null);

        return new self(
            id: Scalars::toInt($data['id'] ?? null),
            name: Scalars::toString($data['name'] ?? null),
            firstname: Scalars::toStringOrNull($data['firstname'] ?? null),
            lastname: Scalars::toStringOrNull($data['lastname'] ?? null),
            age: Scalars::toIntOrNull($data['age'] ?? null),
            birth: Birth::fromArray(Scalars::toMap($data['birth'] ?? null)),
            nationality: Scalars::toStringOrNull($data['nationality'] ?? null),
            height: Scalars::toStringOrNull($data['height'] ?? null),
            weight: Scalars::toStringOrNull($data['weight'] ?? null),
            photo: Scalars::toStringOrNull($data['photo'] ?? null),
            team: $team === [] ? null : TeamRef::fromArray($team),
            career: array_values(array_map(
                static fn(mixed $entry): CoachCareerEntry => CoachCareerEntry::fromArray(Scalars::toMap($entry)),
                $career,
            )),
        );
    }
}
