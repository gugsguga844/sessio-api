<?php

namespace Tests\Feature;

use App\Models\Session;
use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    private function authUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;
        return [$user, $token];
    }

    public function test_create_session()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $payload = [
            'client_id' => $client->id,
            'title' => 'Sessão Teste',
            'start_time' => now()->addHour()->toDateTimeString(),
            'end_time' => now()->addHours(2)->toDateTimeString(),
            'type' => 'presencial',
            'payment_status' => 'pendente',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/sessions', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Sessão Teste']);
    }

    public function test_list_sessions_only_user()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        Session::factory()->count(2)->create(['user_id' => $user->id, 'client_id' => $client->id]);
        Session::factory()->count(2)->create(); // outros usuários
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/sessions');
        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_show_session_authorized()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $session = Session::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/sessions/' . $session->id);
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $session->id]);
    }

    public function test_show_session_unauthorized()
    {
        [$user, $token] = $this->authUser();
        $session = Session::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/sessions/' . $session->id);
        $response->assertStatus(403);
    }

    public function test_update_session()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $session = Session::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $payload = [
            'client_id' => $client->id,
            'title' => 'Sessão Editada',
            'start_time' => now()->addHour()->toDateTimeString(),
            'end_time' => now()->addHours(2)->toDateTimeString(),
            'type' => 'online',
            'payment_status' => 'pago',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/sessions/' . $session->id, $payload);
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Sessão Editada']);
    }

    public function test_delete_session()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $session = Session::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/sessions/' . $session->id);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Evento deletado com sucesso.']);
    }

    public function test_calendar_items()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        Session::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(2),
        ]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/calendar-items?start=' . now()->toDateString() . '&end=' . now()->addDays(2)->toDateString());
        $response->assertStatus(200)
            ->assertJsonStructure([['id', 'item_type', 'start_time', 'end_time']]);
    }
} 