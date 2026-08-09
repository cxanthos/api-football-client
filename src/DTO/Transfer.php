<?php

declare(strict_types=1);

namespace ApiFootball\DTO;

use ApiFootball\Internal\Scalars;

/** `GET /transfers` response item — one player's full transfer history. */
final readonly class Transfer
{
    /**
     * @param list<TransferMove> $transfers
     */
    public function __construct(
        public PlayerRef $player,
        public ?string $update,
        public array $transfers,
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $transfers = Scalars::toArray($data['transfers'] ?? null);

        return new self(
            player: PlayerRef::fromArray(Scalars::toMap($data['player'] ?? null)),
            update: Scalars::toStringOrNull($data['update'] ?? null),
            transfers: array_values(array_map(
                static fn(mixed $item): TransferMove => TransferMove::fromArray(Scalars::toMap($item)),
                $transfers,
            )),
        );
    }
}
