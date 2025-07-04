<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_success()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id', 'name', 'email', 'created_at', 'updated_at'
                ]
            ]);
    }

    public function test_register_validation_error()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['statusCode', 'message', 'errors', 'timestamp', 'path']);
    }

    public function test_login_success()
    {
        $user = User::factory()->create([
            'email' => 'loginuser@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'loginuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id', 'name', 'email', 'created_at', 'updated_at'
                ]
            ]);
    }

    public function test_login_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'wrongpass@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrongpass@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['statusCode', 'message', 'timestamp', 'path']);
    }

    public function test_logout_success()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout realizado com sucesso.']);
    }

    public function test_logout_unauthenticated()
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(401)
            ->assertJsonStructure(['statusCode', 'message', 'timestamp', 'path']);
    }
} 