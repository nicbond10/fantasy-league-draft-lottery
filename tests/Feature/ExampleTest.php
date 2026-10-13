<?php

use App\Models\User;

test('public homepage presents the product and invitation-only sign in', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Make draft night feel official.')
        ->assertSee('Accounts are available by invitation only.')
        ->assertSee('Sign in to continue');
});

test('authenticated homepage links into the lottery application', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('View my lotteries')
        ->assertDontSee('Accounts are available by invitation only.');
});
