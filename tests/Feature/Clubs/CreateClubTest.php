<?php

use App\Enums\Cadence;
use App\Models\Club;
use App\Models\User;

test('an authenticated user can create a club and becomes its owner', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('clubs.store'), [
            'name' => 'Book Nerds',
            'cadence' => 'monthly',
        ])
        ->assertRedirect();

    $club = Club::sole();

    expect($club->name)->toBe('Book Nerds')
        ->and($club->cadence)->toBe(Cadence::Monthly)
        ->and($club->owner->is($user))->toBeTrue();
});

test('a guest cannot create a club', function () {
    $this->post(route('clubs.store'), [
        'name' => 'Book Nerds',
        'cadence' => 'monthly',
    ])->assertRedirect(route('login'));

    expect(Club::count())->toBe(0);
});
