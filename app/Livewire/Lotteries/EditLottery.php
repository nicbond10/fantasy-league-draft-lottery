<?php

declare(strict_types=1);

namespace App\Livewire\Lotteries;

use App\Actions\Lotteries\UpdateLottery as UpdateLotteryAction;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\User;
use App\Support\Percentage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;

#[Title('Edit lottery')]
#[Layout('layouts.app')]
final class EditLottery extends LotteryForm
{
    #[Locked]
    public int $lotteryId;

    public function mount(int $lotteryId): void
    {
        $lottery = $this->ownedLottery($lotteryId, 'update');
        $this->lotteryId = $lottery->id;
        $this->name = $lottery->name;
        $this->mode = $lottery->mode->value;
        $this->presentationMode = $lottery->presentation_mode->value;

        $teams = [];

        foreach ($lottery->teams()->get() as $team) {
            $key = (string) Str::uuid();
            $teams[$key] = [
                'key' => $key,
                'name' => (string) $team->name,
                'abbreviation' => (string) ($team->abbreviation ?? ''),
                'percentage' => Percentage::fromBasisPoints($team->weight),
            ];
        }

        $this->teams = $teams;
    }

    public function saveDraft(UpdateLotteryAction $updateLottery): void
    {
        $lottery = $this->ownedLottery($this->lotteryId, 'update');
        $updateLottery->handle($this->user(), $lottery, $this->dataFor(LotteryStatus::Draft));
        $this->redirectRoute('lotteries.show', ['lotteryId' => $lottery->id], navigate: true);
    }

    public function saveReady(UpdateLotteryAction $updateLottery): void
    {
        $lottery = $this->ownedLottery($this->lotteryId, 'update');
        $updateLottery->handle($this->user(), $lottery, $this->dataFor(LotteryStatus::Ready));
        $this->redirectRoute('lotteries.show', ['lotteryId' => $lottery->id], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.lotteries.edit-lottery');
    }

    private function ownedLottery(int $id, string $ability): Lottery
    {
        $lottery = $this->user()->lotteries()->with('teams')->findOrFail($id);
        Gate::forUser($this->user())->authorize($ability, $lottery);

        return $lottery;
    }

    private function user(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
