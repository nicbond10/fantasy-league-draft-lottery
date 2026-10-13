<?php

use App\Models\User;

test('suspended users cannot access authenticated application pages', function () {
    $user = User::factory()->suspended()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();

    $this->assertGuest();
});
