<?php

use App\Enums\LotteryStatus;
use App\Livewire\Lotteries\CreateLottery;
use App\Livewire\Lotteries\EditLottery;
use App\Livewire\Lotteries\ShowLottery;
use App\Models\Lottery;
use App\Models\LotteryTeam;
use App\Models\User;
use Livewire\Livewire;

test('dynamic team rows can be added and removed without sharing field bindings', function () {
    $user = User::factory()->create();
    $component = Livewire::actingAs($user)->test(CreateLottery::class);
    $originalKeys = array_keys($component->get('teams'));
    [$firstKey, $secondKey] = $originalKeys;

    $component->set("teams.{$firstKey}.name", 'Wolves')
        ->set("teams.{$secondKey}.name", 'Bears')
        ->call('addTeam')
        ->assertCount('teams', 3)
        ->call('removeTeam', $firstKey)
        ->assertCount('teams', 2)
        ->assertSet("teams.{$secondKey}.name", 'Bears');

    expect(array_keys($component->get('teams')))->not->toContain($firstKey)
        ->and(array_column($component->get('teams'), 'key'))->toHaveCount(2)
        ->and(array_unique(array_column($component->get('teams'), 'key')))->toHaveCount(2);
});

test('save draft and mark ready have distinct validation behavior', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(CreateLottery::class);
    [$firstKey, $secondKey] = array_keys($component->get('teams'));

    $component
        ->set('name', 'Incomplete Draft')
        ->set("teams.{$firstKey}.name", 'Wolves')
        ->set("teams.{$secondKey}.name", 'Bears')
        ->call('saveReady')
        ->assertHasErrors(["teams.{$firstKey}.percentage", "teams.{$secondKey}.percentage", 'teams_total'])
        ->call('saveDraft')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect($user->lotteries()->firstOrFail()->status)->toBe(LotteryStatus::Draft);
});

test('blank starter rows are ignored when saving an incomplete draft', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(CreateLottery::class)
        ->set('name', 'Empty Team Draft')
        ->call('saveDraft')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect($user->lotteries()->firstOrFail()->teams()->count())->toBe(0);
});

test('create component validates malformed percentage input', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(CreateLottery::class);
    [$firstKey, $secondKey] = array_keys($component->get('teams'));

    $component
        ->set('name', 'Precision Test')
        ->set("teams.{$firstKey}.name", 'Wolves')
        ->set("teams.{$secondKey}.name", 'Bears')
        ->set("teams.{$firstKey}.percentage", '12.345')
        ->call('saveDraft')
        ->assertHasErrors(["teams.{$firstKey}.percentage"]);
});

test('create component persists a valid ready workflow', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(CreateLottery::class);
    [$firstKey, $secondKey] = array_keys($component->get('teams'));

    $component
        ->set('name', 'Ready Lottery')
        ->set('mode', 'simulation')
        ->set("teams.{$firstKey}.name", 'Wolves')
        ->set("teams.{$firstKey}.percentage", '60.00')
        ->set("teams.{$secondKey}.name", 'Bears')
        ->set("teams.{$secondKey}.percentage", '40.00')
        ->call('saveReady')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect($user->lotteries()->firstOrFail()->status)->toBe(LotteryStatus::Ready);
});

test('edit component preserves initial order and denies archived mutations', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->create();
    LotteryTeam::factory()->for($lottery)->create(['name' => 'Wolves', 'display_order' => 1, 'weight' => 5000]);
    LotteryTeam::factory()->for($lottery)->create(['name' => 'Bears', 'display_order' => 2, 'weight' => 5000]);

    $component = Livewire::actingAs($owner)->test(EditLottery::class, ['lotteryId' => $lottery->id]);
    [$firstKey, $secondKey] = array_keys($component->get('teams'));

    $component->set("teams.{$firstKey}.name", 'Timberwolves')
        ->set("teams.{$secondKey}.name", 'Grizzlies')
        ->call('saveReady')
        ->assertHasNoErrors();

    expect($lottery->teams()->pluck('name')->all())->toBe(['Timberwolves', 'Grizzlies']);

    $lottery->update(['status' => LotteryStatus::Archived, 'archived_at' => now()]);

    $this->actingAs($owner)
        ->get(route('lotteries.edit', ['lotteryId' => $lottery->id]))
        ->assertForbidden();
});

test('show component archives an owned lottery after confirmation path invokes action', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->create();

    Livewire::actingAs($owner)->test(ShowLottery::class, ['lotteryId' => $lottery->id])
        ->call('archive')
        ->assertRedirect(route('lotteries.index'));

    expect($lottery->refresh()->status)->toBe(LotteryStatus::Archived);
});
