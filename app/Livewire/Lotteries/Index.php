<?php

declare(strict_types=1);

namespace App\Livewire\Lotteries;

use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My lotteries')]
#[Layout('layouts.app')]
final class Index extends Component
{
    use WithPagination;

    public string $filter = 'active';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $user = $this->user();
        Gate::forUser($user)->authorize('viewAny', Lottery::class);

        $query = $user->lotteries()->withCount('teams')->latest('updated_at');

        if ($this->filter === 'archived') {
            $query->where('status', LotteryStatus::Archived->value);
        } else {
            $query->where('status', '!=', LotteryStatus::Archived->value);
        }

        return view('livewire.lotteries.index', [
            'lotteries' => $query->paginate(12),
        ]);
    }

    private function user(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
