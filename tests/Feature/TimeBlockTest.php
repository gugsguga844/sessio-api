<?php

namespace Tests\Feature;

use App\Models\TimeBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeBlockTest extends TestCase
{
    use RefreshDatabase;

    private function authUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;
        return [$user, $token];
    }

    public function test_create_time_block()
    {
        [$user, $token] = $this->authUser();
        $payload = [
            'title' => 'Almoço',
            'start_time' => now()->addHour()->toDateTimeString(),
            'end_time' => now()->addHours(2)->toDateTimeString(),
            'color' => '#FF0000',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/time-blocks', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Almoço']);
    }

    public function test_list_time_blocks_only_user()
    {
        [$user, $token] = $this->authUser();
        TimeBlock::factory()->count(2)->create(['user_id' => $user->id]);
        TimeBlock::factory()->count(2)->create(); // outros usuários
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/time-blocks');
        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_show_time_block_authorized()
    {
        [$user, $token] = $this->authUser();
        $block = TimeBlock::factory()->create(['user_id' => $user->id]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/time-blocks/' . $block->id);
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $block->id]);
    }

    public function test_show_time_block_unauthorized()
    {
        [$user, $token] = $this->authUser();
        $otherBlock = TimeBlock::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/time-blocks/' . $otherBlock->id);
        $response->assertStatus(403);
    }

    public function test_update_time_block()
    {
        [$user, $token] = $this->authUser();
        $block = TimeBlock::factory()->create(['user_id' => $user->id]);
        $payload = [
            'title' => 'Reunião',
            'start_time' => now()->addHour()->toDateTimeString(),
            'end_time' => now()->addHours(2)->toDateTimeString(),
            'color' => '#00FF00',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/time-blocks/' . $block->id, $payload);
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Reunião']);
    }

    public function test_delete_time_block()
    {
        [$user, $token] = $this->authUser();
        $block = TimeBlock::factory()->create(['user_id' => $user->id]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/time-blocks/' . $block->id);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Time block deletado com sucesso.']);
    }
} 