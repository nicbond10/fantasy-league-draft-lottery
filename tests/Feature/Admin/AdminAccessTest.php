<?php

use App\Models\User;

test('platform administrators can access administration routes', function () {
    $administrator = User::factory()->platformAdmin()->create();

    $this->actingAs($administrator)
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->get(route('admin.users.index'))->assertOk();
    $this->get(route('admin.invitations.index'))->assertOk();
});

test('non administrators cannot access administration routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->get(route('admin.users.index'))->assertForbidden();
    $this->get(route('admin.invitations.index'))->assertForbidden();
});
