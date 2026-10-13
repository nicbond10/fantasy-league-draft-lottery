<div class="mx-auto max-w-7xl space-y-7">
    <header>
        <p class="eyebrow">{{ __('Administration') }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink">{{ __('Users') }}</h1>
        <p class="mt-2 text-ink-secondary">{{ __('Suspend or reactivate platform access. Suspension immediately blocks authenticated requests.') }}</p>
    </header>

    @error('user')
        <p class="rounded-xl border border-danger/35 bg-danger/10 p-4 text-sm text-rose-200" role="alert">{{ $message }}</p>
    @enderror

    <div class="app-card overflow-x-auto">
        <table class="w-full min-w-[46rem] text-left text-sm">
            <thead class="bg-raised text-ink-muted">
                <tr>
                    <th class="px-4 py-3 font-medium sm:px-6" scope="col">{{ __('User') }}</th>
                    <th class="px-4 py-3 font-medium" scope="col">{{ __('Access level') }}</th>
                    <th class="px-4 py-3 font-medium" scope="col">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right font-medium sm:px-6" scope="col">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="px-4 py-4 sm:px-6">
                            <div class="font-semibold text-ink">{{ $user->name }}</div>
                            <div class="mt-1 text-ink-secondary">{{ $user->email }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $user->is_platform_admin ? 'border-brand/35 bg-brand/10 text-rose-200' : 'border-line bg-raised text-ink-secondary' }}">
                                {{ $user->is_platform_admin ? __('Platform administrator') : __('User') }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $user->status === $activeStatus ? 'border-success/35 bg-success/10 text-emerald-300' : 'border-danger/35 bg-danger/10 text-rose-200' }}">
                                {{ ucfirst($user->status->value) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right sm:px-6">
                            @if (auth()->id() === $user->id)
                                <span class="text-xs font-medium text-ink-muted">{{ __('Current account') }}</span>
                            @elseif ($user->status === $activeStatus)
                                <flux:button size="sm" variant="danger" wire:click="suspend({{ $user->id }})" wire:loading.attr="disabled" wire:target="suspend({{ $user->id }})" wire:confirm="{{ __('Suspend :name? They will immediately lose platform access.', ['name' => $user->name]) }}">{{ __('Suspend') }}</flux:button>
                            @else
                                <flux:button size="sm" wire:click="reactivate({{ $user->id }})" wire:loading.attr="disabled" wire:target="reactivate({{ $user->id }})">{{ __('Reactivate') }}</flux:button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
