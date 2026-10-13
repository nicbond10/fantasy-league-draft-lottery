<?php

use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Dashboard;
use App\Livewire\Lotteries\CreateLottery;
use App\Livewire\Lotteries\EditLottery;
use App\Livewire\Lotteries\Index as LotteryIndex;
use App\Livewire\Lotteries\ShowLottery;
use Illuminate\Support\Facades\Route;

Route::view('/', 'index')->name('home');

Route::livewire('invitations/accept/{token}', AcceptInvitation::class)
    ->name('invitations.accept');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');
    Route::livewire('lotteries', LotteryIndex::class)->name('lotteries.index');
    Route::livewire('lotteries/create', CreateLottery::class)->name('lotteries.create');
    Route::livewire('lotteries/{lotteryId}/edit', EditLottery::class)->name('lotteries.edit');
    Route::livewire('lotteries/{lotteryId}', ShowLottery::class)->name('lotteries.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
