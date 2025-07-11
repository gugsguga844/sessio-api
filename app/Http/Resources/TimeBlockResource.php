<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="TimeBlock",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", maxLength=100, example="Almoço"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2023-01-01T12:00:00-03:00"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2023-01-01T13:00:00-03:00"),
 *     @OA\Property(property="color_hex", type="string", maxLength=7, nullable=true, example="#FF5733"),
 *     @OA\Property(property="emoji", type="string", maxLength=5, nullable=true, example="🍽️"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="item_type", type="string", example="block")
 * )
 */
class TimeBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'start_time' => $this->start_time->toIso8601String(),
            'end_time' => $this->end_time->toIso8601String(),
            'color_hex' => $this->color_hex,
            'emoji' => $this->emoji,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'item_type' => 'block', // Para identificar o tipo de item na resposta da API
            'user' => $this->whenLoaded('user'),
        ];
    }
}
