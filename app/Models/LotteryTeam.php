<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LotteryTeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $lottery_id
 * @property string $name
 * @property string|null $abbreviation
 * @property int $weight
 * @property int $display_order
 */
#[Fillable(['name', 'abbreviation', 'weight', 'display_order'])]
class LotteryTeam extends Model
{
    /** @use HasFactory<LotteryTeamFactory> */
    use HasFactory;

    /** @return BelongsTo<Lottery, $this> */
    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'display_order' => 'integer',
        ];
    }
}
