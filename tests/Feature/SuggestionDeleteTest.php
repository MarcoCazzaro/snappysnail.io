<?php

use App\Models\Suggestion;
use App\Models\User;

it('deletes a suggestion and redirects to index', function () {
    $user = User::factory()->create();
    $suggestion = Suggestion::factory()->create();

    $this->actingAs($user)
        ->delete(route('suggestions.destroy', $suggestion))
        ->assertRedirect(route('suggestions.index'));

    expect(Suggestion::find($suggestion->id))->toBeNull();
});

it('requires authentication to delete a suggestion', function () {
    $suggestion = Suggestion::factory()->create();

    $this->delete(route('suggestions.destroy', $suggestion))
        ->assertRedirect(route('login'));

    expect(Suggestion::find($suggestion->id))->not->toBeNull();
});
