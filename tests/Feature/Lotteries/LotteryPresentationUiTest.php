<?php

use App\Actions\Lotteries\RunOfficialLotteryDraw;
use App\Enums\LotteryMode;
use App\Enums\LotteryStatus;
use App\Livewire\Lotteries\ShowLottery;
use App\Models\User;
use Livewire\Livewire;

test('lottery forms explain official and simulation semantics and expose ordered controls', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('lotteries.create'))
        ->assertOk()
        ->assertSee('Runs once and saves a definitive result')
        ->assertSee('Simulation results are not saved as official results')
        ->assertSee('Official reveal mode')
        ->assertSee('This setting applies only to official lottery presentations and replays')
        ->assertSee('Add teams in their initial draft order')
        ->assertDontSee('Move team up')
        ->assertDontSee('Move team down')
        ->assertSee('Save Draft')
        ->assertSee('Save and Mark Ready');
});

test('official ready page has an accessible irreversible confirmation and presentation controls', function () {
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);

    $this->actingAs($owner)
        ->get(route('lotteries.show', ['lotteryId' => $lottery->id]))
        ->assertOk()
        ->assertSee('Run Official Lottery')
        ->assertSee('Run the official lottery?')
        ->assertSee('Start Lottery')
        ->assertSee('Show Final Result')
        ->assertSee('Skip Presentation')
        ->assertSee('Close presentation')
        ->assertDontSee('Simulate Lottery');
});

test('simulation ready page clearly marks results as transient and never offers an official draw', function () {
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner, LotteryMode::Simulation);

    $this->actingAs($owner)
        ->get(route('lotteries.show', ['lotteryId' => $lottery->id]))
        ->assertSee('Simulation results are temporary')
        ->assertSee('Simulate Lottery')
        ->assertSee('Not an official saved result')
        ->assertSee('Temporary simulation results from this page session')
        ->assertSee('Reset simulation stats')
        ->assertDontSee('Run the official lottery?');
});

test('completed official page renders its saved full order and never exposes rerun', function () {
    useRandomNumbers([8000]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);
    app(RunOfficialLotteryDraw::class)->handle($owner, $lottery);

    $this->actingAs($owner)
        ->get(route('lotteries.show', ['lotteryId' => $lottery->id]))
        ->assertSeeInOrder(['Raptors', 'Wolves', 'Bears'])
        ->assertSee('First overall pick winner')
        ->assertSee('Replay Presentation')
        ->assertSee('Start Replay')
        ->assertSee('Initiated by')
        ->assertDontSee('Reset simulation stats')
        ->assertDontSee('Run Official Lottery');
});

test('archived lottery is read only while retaining a completed saved order', function () {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);
    app(RunOfficialLotteryDraw::class)->handle($owner, $lottery);
    $lottery->update(['status' => LotteryStatus::Archived, 'archived_at' => now()]);

    Livewire::actingAs($owner)->test(ShowLottery::class, ['lotteryId' => $lottery->id])
        ->assertSee('Archived record')
        ->assertSee('Final draft order')
        ->assertSee('First overall pick winner')
        ->assertDontSee('Edit Lottery')
        ->assertSee('Execution and active replay are disabled');
});

test('a second simulation publishes another complete server result', function () {
    useRandomNumbers([1, 8000]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner, LotteryMode::Simulation);
    $component = Livewire::actingAs($owner)->test(ShowLottery::class, ['lotteryId' => $lottery->id]);

    $component->call('runSimulation')
        ->assertDispatched('lottery-result-ready', fn (string $event, array $params): bool => $params['source'] === 'simulation'
            && $params['result']['winner']['team_name'] === 'Wolves'
            && is_string($params['result']['simulation_event_id'])
            && count($params['result']['positions']) === 3);

    $component->call('runSimulation')
        ->assertDispatched('lottery-result-ready', fn (string $event, array $params): bool => $params['source'] === 'simulation'
            && $params['result']['winner']['team_name'] === 'Raptors'
            && is_string($params['result']['simulation_event_id'])
            && count($params['result']['positions']) === 3);
});

test('presentation derives a copied descending sequence and keeps manual start for reduced motion', function () {
    $module = file_get_contents(resource_path('js/lottery-presentation.js'));

    expect($module)
        ->toContain("state = 'ready'")
        ->toContain("revealMode: 'manual'")
        ->toContain('autoplayDelayMs: 3000')
        ->toContain('this.reducedMotion ? 700 : this.autoplayDelayMs')
        ->toContain('[...(this.result?.positions ?? [])]')
        ->toContain('first.final_position - second.final_position')
        ->toContain('[...this.orderedPositions()].reverse()')
        ->not->toContain('result.positions.reverse()');
});

test('presentation renders semantic modes fixed placeholders and one-pick manual control', function () {
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);

    $this->actingAs($owner)
        ->get(route('lotteries.show', ['lotteryId' => $lottery->id]))
        ->assertSee('Presentation mode')
        ->assertSee('Manual')
        ->assertSee('Autoplay')
        ->assertSee('Not revealed')
        ->assertSee('revealNextManual', escape: false)
        ->assertSee('revealButtonLabel()', escape: false);
});

test('design system centrally assigns pointer and disabled cursors to semantic controls', function () {
    $styles = file_get_contents(resource_path('css/app.css'));

    expect($styles)
        ->toContain('button:not(:disabled)')
        ->toContain('a[href]')
        ->toContain("[role='radio']:not([aria-disabled='true'])")
        ->toContain('cursor: pointer')
        ->toContain('button:disabled')
        ->toContain('cursor: not-allowed');
});

test('simulation reruns keep the presentation dialog open while awaiting the next result', function () {
    $module = file_get_contents(resource_path('js/lottery-presentation.js'));
    $method = str($module)->after('async simulateAgain()')->before('recordSimulation(result, source)');

    expect((string) $method)
        ->toContain('await this.$wire.runSimulation()')
        ->toContain('simulationPending = true')
        ->not->toContain('this.close()');
});
