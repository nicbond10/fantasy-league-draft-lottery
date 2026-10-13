<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\LotteryMode;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\User;

final class LotteryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Lottery $lottery): bool
    {
        return $user->isActive() && $lottery->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Lottery $lottery): bool
    {
        return $this->view($user, $lottery) && $lottery->isEditable();
    }

    public function archive(User $user, Lottery $lottery): bool
    {
        return $this->view($user, $lottery) && $lottery->status !== LotteryStatus::Archived;
    }

    public function runOfficialDraw(User $user, Lottery $lottery): bool
    {
        return $this->view($user, $lottery)
            && $lottery->mode === LotteryMode::Official
            && $lottery->status === LotteryStatus::Ready
            && ! $lottery->officialDraw()->exists();
    }

    public function simulate(User $user, Lottery $lottery): bool
    {
        return $this->view($user, $lottery)
            && $lottery->mode === LotteryMode::Simulation
            && $lottery->status === LotteryStatus::Ready;
    }

    public function replay(User $user, Lottery $lottery): bool
    {
        return $this->view($user, $lottery)
            && in_array($lottery->status, [LotteryStatus::Completed, LotteryStatus::Archived], true)
            && $lottery->officialDraw()->exists();
    }
}
