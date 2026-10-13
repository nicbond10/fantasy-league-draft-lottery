<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Actions\Users\ReactivateUser;
use App\Actions\Users\SuspendUser;
use App\Enums\UserStatus;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manage users')]
#[Layout('layouts.app')]
final class Index extends Component
{
    use WithPagination;

    public function suspend(int $userId, SuspendUser $suspendUser): void
    {
        $administrator = auth()->user();
        abort_unless($administrator instanceof User, 403);

        $suspendUser->handle($administrator, User::query()->findOrFail($userId));
        Flux::toast(variant: 'success', text: 'User suspended.');
    }

    public function reactivate(int $userId, ReactivateUser $reactivateUser): void
    {
        $administrator = auth()->user();
        abort_unless($administrator instanceof User, 403);

        $reactivateUser->handle($administrator, User::query()->findOrFail($userId));
        Flux::toast(variant: 'success', text: 'User reactivated.');
    }

    public function render(): View
    {
        return view('livewire.admin.users.index', [
            'users' => User::query()
                ->select(['id', 'name', 'email', 'status', 'is_platform_admin', 'created_at'])
                ->orderBy('name')
                ->paginate(15),
            'activeStatus' => UserStatus::Active,
        ]);
    }
}
