<?php

use App\Actions\Lotteries\ArchiveLottery;
use App\Livewire\Lotteries\EditLottery;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('the lottery index only lists the authenticated users lotteries', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Lottery::factory()->for($user, 'owner')->create(['name' => 'My Private Lottery']);
    Lottery::factory()->for($otherUser, 'owner')->create(['name' => 'Someone Else Lottery']);

    $this->actingAs($user)
        ->get(route('lotteries.index'))
        ->assertOk()
        ->assertSee('My Private Lottery')
        ->assertDontSee('Someone Else Lottery');
});

test('a user cannot view or edit another users lottery by crafted id', function () {
    $user = User::factory()->create();
    $lottery = Lottery::factory()->create();

    $this->actingAs($user)
        ->get(route('lotteries.show', ['lotteryId' => $lottery->id]))
        ->assertNotFound();

    $this->get(route('lotteries.edit', ['lotteryId' => $lottery->id]))
        ->assertNotFound();

    expect(fn () => Livewire::actingAs($user)->test(EditLottery::class, ['lotteryId' => $lottery->id]))
        ->toThrow(ModelNotFoundException::class);
});

test('a user cannot archive another users lottery', function () {
    $user = User::factory()->create();
    $lottery = Lottery::factory()->create();

    expect(fn () => app(ArchiveLottery::class)->handle($user, $lottery))
        ->toThrow(AuthorizationException::class);
});

test('platform administrators have no implicit access to user lotteries', function () {
    $administrator = User::factory()->platformAdmin()->create();
    $lottery = Lottery::factory()->create();

    $this->actingAs($administrator)
        ->get(route('lotteries.show', ['lotteryId' => $lottery->id]))
        ->assertNotFound();

    expect(fn () => app(ArchiveLottery::class)->handle($administrator, $lottery))
        ->toThrow(AuthorizationException::class);
});

test('suspended users remain blocked from lottery pages', function () {
    $user = User::factory()->suspended()->create();

    $this->actingAs($user)
        ->get(route('lotteries.index'))
        ->assertForbidden();
});
