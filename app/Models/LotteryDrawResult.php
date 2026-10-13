<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LotteryDrawResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $lottery_draw_id
 * @property int|null $lottery_team_id
 * @property int $selection_position
 * @property array<string, mixed> $team_snapshot
 */
#[Fillable(['lottery_team_id', 'selection_position', 'team_snapshot'])]
class LotteryDrawResult extends Model
{
    /** @use HasFactory<LotteryDrawResultFactory> */
    use HasFactory;

    /** @return BelongsTo<LotteryDraw, $this> */
    public function draw(): BelongsTo
    {
        return $this->belongsTo(LotteryDraw::class, 'lottery_draw_id');
    }

    /** @return BelongsTo<LotteryTeam, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(LotteryTeam::class, 'lottery_team_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'selection_position' => 'integer',
            'team_snapshot' => 'array',
        ];
    }
}
