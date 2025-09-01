<?php

use App\Models\User;

test('guest cannot view instrument set index and is redirected to login', function () {
    $response = $this->get('/instrumentset');

    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

test('guest cannot view instrument set create page and is redirected to login', function () {
    $response = $this->get('/instrumentset/create');

    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

test('authenticated user can view instrument set index and create page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $index = $this->get('/instrumentset');
    $index->assertStatus(200);

    $create = $this->get('/instrumentset/create');
    $create->assertStatus(200);
});
