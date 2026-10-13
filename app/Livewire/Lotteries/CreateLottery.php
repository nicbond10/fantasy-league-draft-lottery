<?php

declare(strict_types=1);

namespace App\Livewire\Lotteries;

use App\Actions\Lotteries\CreateLottery as CreateLotteryAction;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('New lottery')]
#[Layout('layouts.app')]
final class CreateLottery extends LotteryForm
{
    public function mount(): void
    {
        Gate::forUser($this->user())->authorize('create', Lottery::class);
        $this->addTeam();
        $this->addTeam();
    }

    public function saveDraft(CreateLotteryAction $createLottery): void
    {
        $lottery = $createLottery->handle($this->user(), $this->dataFor(LotteryStatus::Draft));
        $this->redirectRoute('lotteries.show', ['lotteryId' => $lottery->id], navigate: true);
    }

    public function saveReady(CreateLotteryAction $createLottery): void
    {
        $lottery = $createLottery->handle($this->user(), $this->dataFor(LotteryStatus::Ready));
        $this->redirectRoute('lotteries.show', ['lotteryId' => $lottery->id], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.lotteries.create-lottery');
    }

    private function user(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
