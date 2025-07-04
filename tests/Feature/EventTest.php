<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    private function authUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;
        return [$user, $token];
    }

    public function test_create_event()
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
            ->postJson('/api/events', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Sessão Teste']);
    }

    public function test_list_events_only_user()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        Event::factory()->count(2)->create(['user_id' => $user->id, 'client_id' => $client->id]);
        Event::factory()->count(2)->create(); // outros usuários
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/events');
        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_show_event_authorized()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $event = Event::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/events/' . $event->id);
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $event->id]);
    }

    public function test_show_event_unauthorized()
    {
        [$user, $token] = $this->authUser();
        $event = Event::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/events/' . $event->id);
        $response->assertStatus(403);
    }

    public function test_update_event()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $event = Event::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $payload = [
            'client_id' => $client->id,
            'title' => 'Sessão Editada',
            'start_time' => now()->addHour()->toDateTimeString(),
            'end_time' => now()->addHours(2)->toDateTimeString(),
            'type' => 'online',
            'payment_status' => 'pago',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/events/' . $event->id, $payload);
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Sessão Editada']);
    }

    public function test_delete_event()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $event = Event::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/events/' . $event->id);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Evento deletado com sucesso.']);
    }

    public function test_calendar_items()
    {
        [$user, $token] = $this->authUser();
        $client = Client::factory()->create(['user_id' => $user->id]);
        Event::factory()->create([
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