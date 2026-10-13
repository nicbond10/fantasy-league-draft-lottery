<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LotteryDrawFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $lottery_id
 * @property int $initiated_by_user_id
 * @property string $algorithm_version
 * @property array<string, mixed> $configuration_snapshot
 * @property Carbon $completed_at
 */
#[Fillable(['initiated_by_user_id', 'algorithm_version', 'configuration_snapshot', 'completed_at'])]
class LotteryDraw extends Model
{
    /** @use HasFactory<LotteryDrawFactory> */
    use HasFactory;

    /** @return BelongsTo<Lottery, $this> */
    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    /** @return BelongsTo<User, $this> */
    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    /** @return HasMany<LotteryDrawResult, $this> */
    public function results(): HasMany
    {
        return $this->hasMany(LotteryDrawResult::class)->orderBy('selection_position');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'configuration_snapshot' => 'array',
            'completed_at' => 'immutable_datetime',
        ];
    }
}
