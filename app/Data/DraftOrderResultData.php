<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\LotteryFormat;
use App\Enums\LotteryMode;
use DateTimeInterface;
use LogicException;

final readonly class DraftOrderResultData
{
    /**
     * @param  list<DraftPositionData>  $positions
     * @param  array{id: int, name: string}|null  $initiatedBy
     */
    public function __construct(
        public int $lotteryId,
        public string $lotteryName,
        public LotteryMode $mode,
        public LotteryFormat $format,
        public string $algorithmVersion,
        public bool $official,
        public string $source,
        public array $positions,
        public ?DateTimeInterface $completedAt = null,
        public ?array $initiatedBy = null,
    ) {}

    public function winner(): DraftPositionData
    {
        foreach ($this->positions as $position) {
            if ($position->winner) {
                return $position;
            }
        }

        throw new LogicException('The draft order does not contain a weighted winner.');
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'lottery_id' => $this->lotteryId,
            'lottery_name' => $this->lotteryName,
            'mode' => $this->mode->value,
            'format' => $this->format->value,
            'algorithm_version' => $this->algorithmVersion,
            'official' => $this->official,
            'source' => $this->source,
            'positions' => array_map(static fn (DraftPositionData $position): array => $position->toArray(), $this->positions),
            'winner' => $this->winner()->toArray(),
            'completed_at' => $this->completedAt?->format(DateTimeInterface::ATOM),
            'initiated_by' => $this->initiatedBy,
        ];
    }

    /** @param array{id: int, name: string} $initiatedBy */
    public function asOfficial(DateTimeInterface $completedAt, array $initiatedBy): self
    {
        return new self(
            lotteryId: $this->lotteryId,
            lotteryName: $this->lotteryName,
            mode: $this->mode,
            format: $this->format,
            algorithmVersion: $this->algorithmVersion,
            official: true,
            source: 'official',
            positions: $this->positions,
            completedAt: $completedAt,
            initiatedBy: $initiatedBy,
        );
    }
}
