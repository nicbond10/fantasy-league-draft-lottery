<?php

declare(strict_types=1);

namespace App\Livewire\Lotteries;

use App\Actions\Lotteries\ArchiveLottery;
use App\Actions\Lotteries\GetLotteryReplay;
use App\Actions\Lotteries\RunLotterySimulation;
use App\Actions\Lotteries\RunOfficialLotteryDraw;
use App\Enums\LotteryStatus;
use App\Exceptions\LotteryCannotBeRun;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Lottery details')]
#[Layout('layouts.app')]
final class ShowLottery extends Component
{
    #[Locked]
    public int $lotteryId;

    /** @var array<string, mixed>|null */
    public ?array $result = null;

    public function mount(int $lotteryId, GetLotteryReplay $getLotteryReplay): void
    {
        $lottery = $this->ownedLottery($lotteryId);
        Gate::forUser($this->user())->authorize('view', $lottery);
        $this->lotteryId = $lottery->id;

        if ($lottery->officialDraw()->exists()
            && in_array($lottery->status, [LotteryStatus::Completed, LotteryStatus::Archived], true)) {
            $this->result = $getLotteryReplay->handle($this->user(), $lottery)->toArray();
        }
    }

    public function runOfficialDraw(RunOfficialLotteryDraw $runOfficialLotteryDraw): void
    {
        $lottery = $this->ownedLottery($this->lotteryId);
        Gate::forUser($this->user())->authorize('view', $lottery);

        if (! $this->consumeRateLimit('official-draw', 5)) {
            return;
        }

        try {
            $result = $runOfficialLotteryDraw->handle($this->user(), $lottery);
        } catch (LotteryCannotBeRun $exception) {
            $this->addError('execution', $exception->getMessage());

            return;
        }

        $this->publishResult($result->toArray(), 'official', $lottery->presentation_mode->value);
    }

    public function runSimulation(RunLotterySimulation $runLotterySimulation): void
    {
        $lottery = $this->ownedLottery($this->lotteryId);
        Gate::forUser($this->user())->authorize('view', $lottery);

        if (! $this->consumeRateLimit('simulation', 60)) {
            return;
        }

        try {
            $result = $runLotterySimulation->handle($this->user(), $lottery);
        } catch (LotteryCannotBeRun $exception) {
            $this->addError('execution', $exception->getMessage());

            return;
        }

        $this->publishResult($result->toArray(), 'simulation', $lottery->presentation_mode->value);
    }

    public function replay(GetLotteryReplay $getLotteryReplay): void
    {
        $lottery = $this->ownedLottery($this->lotteryId);
        Gate::forUser($this->user())->authorize('view', $lottery);

        if ($lottery->status === LotteryStatus::Archived) {
            $this->addError('execution', 'Archived results remain viewable but cannot start an active replay.');

            return;
        }

        try {
            $result = $getLotteryReplay->handle($this->user(), $lottery);
        } catch (LotteryCannotBeRun $exception) {
            $this->addError('execution', $exception->getMessage());

            return;
        }

        $this->publishResult($result->toArray(), 'replay', $lottery->presentation_mode->value);
    }

    public function archive(ArchiveLottery $archiveLottery): void
    {
        $lottery = $this->ownedLottery($this->lotteryId);
        Gate::forUser($this->user())->authorize('archive', $lottery);
        $archiveLottery->handle($this->user(), $lottery);
        $this->redirectRoute('lotteries.index', navigate: true);
    }

    public function render(): View
    {
        $lottery = $this->ownedLottery($this->lotteryId);
        Gate::forUser($this->user())->authorize('view', $lottery);

        return view('livewire.lotteries.show-lottery', ['lottery' => $lottery]);
    }

    private function ownedLottery(int $id): Lottery
    {
        return $this->user()->lotteries()->with('teams')->findOrFail($id);
    }

    private function user(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function consumeRateLimit(string $action, int $maximumAttempts): bool
    {
        $key = "lottery:{$action}:{$this->user()->id}:{$this->lotteryId}";

        if (RateLimiter::tooManyAttempts($key, $maximumAttempts)) {
            $this->addError('execution', 'Too many attempts. Please wait a minute and try again.');

            return false;
        }

        RateLimiter::hit($key, 60);

        return true;
    }

    /** @param array<string, mixed> $result */
    private function publishResult(array $result, string $source, string $presentationMode): void
    {
        if ($source === 'simulation') {
            $result['simulation_event_id'] = (string) Str::uuid();
        }

        $this->result = $result;
        $this->resetErrorBag('execution');
        $this->dispatch(
            'lottery-result-ready',
            result: $result,
            source: $source,
            presentationMode: $presentationMode,
        );
    }
}
