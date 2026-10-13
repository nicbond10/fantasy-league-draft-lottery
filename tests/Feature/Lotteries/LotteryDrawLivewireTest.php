<?php

use App\Actions\Lotteries\RunOfficialLotteryDraw;
use App\Enums\LotteryMode;
use App\Enums\LotteryPresentationMode;
use App\Enums\LotteryStatus;
use App\Livewire\Lotteries\ShowLottery;
use App\Models\LotteryDraw;
use App\Models\User;
use Livewire\Livewire;

test('lottery details show controls appropriate to mode and status', function () {
    $owner = User::factory()->create();
    $official = configuredLottery($owner);
    $simulation = configuredLottery($owner, LotteryMode::Simulation);
    $draft = configuredLottery($owner, LotteryMode::Official, LotteryStatus::Draft);

    $this->actingAs($owner)
        ->get(route('lotteries.show', ['lotteryId' => $official->id]))
        ->assertSee('Run Official Lottery')
        ->assertDontSee('Simulate Lottery');

    $this->get(route('lotteries.show', ['lotteryId' => $simulation->id]))
        ->assertSee('Simulate Lottery')
        ->assertDontSee('Run Official Lottery');

    $this->get(route('lotteries.show', ['lotteryId' => $draft->id]))
        ->assertSee('mark this lottery ready')
        ->assertDontSee('Run Official Lottery');
});

test('official Livewire action dispatches the complete result event', function () {
    useRandomNumbers([8000]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);

    Livewire::actingAs($owner)->test(ShowLottery::class, ['lotteryId' => $lottery->id])
        ->call('runOfficialDraw')
        ->assertHasNoErrors()
        ->assertDispatched('lottery-result-ready', function (string $name, array $params): bool {
            return $params['source'] === 'official'
                && count($params['result']['positions']) === 3
                && $params['result']['positions'][0]['winner'] === true;
        });

    expect(LotteryDraw::query()->count())->toBe(1)
        ->and($lottery->refresh()->status)->toBe(LotteryStatus::Completed);
});

test('simulation Livewire action dispatches transient complete results repeatedly', function () {
    useRandomNumbers([1, 8000]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner, LotteryMode::Simulation);
    $component = Livewire::actingAs($owner)->test(ShowLottery::class, ['lotteryId' => $lottery->id]);

    $component->call('runSimulation')
        ->assertDispatched('lottery-result-ready', fn (string $name, array $params): bool => $params['source'] === 'simulation' && count($params['result']['positions']) === 3)
        ->call('runSimulation')
        ->assertHasNoErrors();

    expect(LotteryDraw::query()->count())->toBe(0)
        ->and($lottery->refresh()->status)->toBe(LotteryStatus::Ready);
});

test('completed lottery can replay while repeated official execution shows a clear error', function () {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);
    $component = Livewire::actingAs($owner)->test(ShowLottery::class, ['lotteryId' => $lottery->id]);

    $component->call('runOfficialDraw')->assertHasNoErrors();
    $component->call('runOfficialDraw')->assertHasErrors(['execution']);
    $component->call('replay')
        ->assertHasNoErrors()
        ->assertDispatched('lottery-result-ready', fn (string $name, array $params): bool => $params['source'] === 'replay' && count($params['result']['positions']) === 3);

    expect(LotteryDraw::query()->count())->toBe(1);
});

test('archived completed result is displayed but active replay is disabled', function () {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);
    app(RunOfficialLotteryDraw::class)->handle($owner, $lottery);
    $lottery->refresh()->update(['status' => LotteryStatus::Archived, 'archived_at' => now()]);

    Livewire::actingAs($owner)->test(ShowLottery::class, ['lotteryId' => $lottery->id])
        ->assertSee('Final draft order')
        ->assertDontSee('Replay Result')
        ->call('replay')
        ->assertHasErrors(['execution'])
        ->assertNotDispatched('lottery-result-ready');
});

test('official draw and replay events expose the lottery configured presentation mode', function () {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner, presentationMode: LotteryPresentationMode::Autoplay);
    $component = Livewire::actingAs($owner)->test(ShowLottery::class, ['lotteryId' => $lottery->id]);

    $component->call('runOfficialDraw')
        ->assertDispatched('lottery-result-ready', fn (string $event, array $params): bool => $params['source'] === 'official'
            && $params['presentationMode'] === 'autoplay');

    $component->call('replay')
        ->assertDispatched('lottery-result-ready', fn (string $event, array $params): bool => $params['source'] === 'replay'
            && $params['presentationMode'] === 'autoplay');
});
