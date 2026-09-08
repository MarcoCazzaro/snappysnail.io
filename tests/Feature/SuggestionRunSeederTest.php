<?php

use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('runs the suggestions seeder and flashes its output', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('suggestions.run-seeder'))
        ->assertRedirect(route('suggestions.index'))
        ->assertSessionHas('seederOutput');

    expect(Suggestion::withoutGlobalScopes()->where('key', 'contact')->exists())->toBeTrue();
});

it('requires authentication to run the seeder', function () {
    $this->post(route('suggestions.run-seeder'))
        ->assertRedirect(route('login'));
});
