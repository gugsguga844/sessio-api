<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private function authUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;
        return [$user, $token];
    }

    public function test_create_client()
    {
        [$user, $token] = $this->authUser();
        $payload = [
            'full_name' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'phone' => '11999999999',
            'status' => 'active',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/clients', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['full_name' => 'Cliente Teste']);
    }

    public function test_list_clients_only_user()
    {
        [$user, $token] = $this->authUser();
        Client::factory()->count(2)->create(['user_id' => $user->id]);
        Client::factory()->count(2)->create(); // outros usuários
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/clients');
        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_show_client_authorized()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/clients/' . $client->id);
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $client->id]);
    }

    public function test_show_client_unauthorized()
    {
        [$user, $token] = $this->authUser();
        $otherClient = Client::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/clients/' . $otherClient->id);
        $response->assertStatus(403);
    }

    public function test_update_client()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $payload = ['full_name' => 'Novo Nome', 'email' => 'novo@email.com', 'phone' => '111', 'status' => 'inactive'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/clients/' . $client->id, $payload);
        $response->assertStatus(200)
            ->assertJsonFragment(['full_name' => 'Novo Nome']);
    }

    public function test_delete_client()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/clients/' . $client->id);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Cliente deletado com sucesso.']);
    }
} 