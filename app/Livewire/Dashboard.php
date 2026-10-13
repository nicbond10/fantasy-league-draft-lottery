<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\LotteryMode;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\LotteryDraw;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
#[Layout('layouts.app')]
final class Dashboard extends Component
{
    public function render(): View
    {
        $user = $this->user();
        Gate::forUser($user)->authorize('viewAny', Lottery::class);
        $counts = $user->lotteries()
            ->selectRaw('SUM(CASE WHEN status != ? THEN 1 ELSE 0 END) as active', [LotteryStatus::Archived->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as ready', [LotteryStatus::Ready->value])
            ->selectRaw('SUM(CASE WHEN mode = ? THEN 1 ELSE 0 END) as simulations', [LotteryMode::Simulation->value])
            ->first();

        return view('dashboard', [
            'activeCount' => (int) $counts?->getAttribute('active'),
            'readyCount' => (int) $counts?->getAttribute('ready'),
            'completedOfficialCount' => LotteryDraw::query()
                ->whereHas('lottery', fn ($query) => $query->where('user_id', $user->id))
                ->count(),
            'simulationCount' => (int) $counts?->getAttribute('simulations'),
            'recentLotteries' => $user->lotteries()->withCount('teams')->latest('updated_at')->limit(5)->get(),
        ]);
    }

    private function user(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
