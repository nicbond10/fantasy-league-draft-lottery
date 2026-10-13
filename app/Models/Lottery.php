<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LotteryFormat;
use App\Enums\LotteryMode;
use App\Enums\LotteryPresentationMode;
use App\Enums\LotteryStatus;
use Database\Factories\LotteryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property LotteryMode $mode
 * @property LotteryFormat $format
 * @property LotteryPresentationMode $presentation_mode
 * @property LotteryStatus $status
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'mode', 'format', 'presentation_mode', 'status', 'archived_at'])]
class Lottery extends Model
{
    /** @use HasFactory<LotteryFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<LotteryTeam, $this> */
    public function teams(): HasMany
    {
        return $this->hasMany(LotteryTeam::class)->orderBy('display_order');
    }

    /** @return HasOne<LotteryDraw, $this> */
    public function officialDraw(): HasOne
    {
        return $this->hasOne(LotteryDraw::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [LotteryStatus::Draft, LotteryStatus::Ready], true);
    }

    public function isCompleted(): bool
    {
        return $this->status === LotteryStatus::Completed;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'mode' => LotteryMode::class,
            'format' => LotteryFormat::class,
            'presentation_mode' => LotteryPresentationMode::class,
            'status' => LotteryStatus::class,
            'archived_at' => 'immutable_datetime',
        ];
    }
}
