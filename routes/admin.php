<?php

declare(strict_types=1);

use App\Http\Middleware\EnsurePlatformAdministrator;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Invitations\Index as InvitationsIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', EnsurePlatformAdministrator::class])
    ->group(function (): void {
        Route::livewire('/', Dashboard::class)->name('dashboard');
        Route::livewire('/users', UsersIndex::class)->name('users.index');
        Route::livewire('/invitations', InvitationsIndex::class)->name('invitations.index');
    });
