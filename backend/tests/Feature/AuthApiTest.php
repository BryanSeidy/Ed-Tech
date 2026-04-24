<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_me_and_logout_session_flow(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Assom Dev',
            'email' => 'assom@example.com',
            'password' => 'password123',
        ]);

        $register->assertCreated()->assertJsonPath('data.email', 'assom@example.com');

        $login = $this->postJson('/api/auth/login', [
            'email' => 'assom@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk()->assertJsonPath('data.email', 'assom@example.com');

        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'assom@example.com');

        $this->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Déconnecté avec succès.');

        $this->getJson('/api/auth/me')
            ->assertStatus(401)
            ->assertJsonPath('code', 'unauthenticated');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Assom Dev',
            'email' => 'assom@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'assom@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)->assertJsonPath('code', 'validation_error');
    }
}
