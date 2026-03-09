<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_created_user_payload(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Assom Dev',
            'email' => 'assom@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'avatar'],
                'user' => ['id', 'name', 'email', 'avatar'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'assom@example.com']);
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

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }
}
