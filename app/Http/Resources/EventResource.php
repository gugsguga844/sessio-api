<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Event",
 *     title="Event",
 *     description="Event model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(
 *         property="participants",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="full_name", type="string", example="João da Silva"),
 *             @OA\Property(property="email", type="string", example="joao@email.com")
 *         )
 *     ),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2025-07-08T14:00:00Z"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2025-07-08T15:00:00Z"),
 *     @OA\Property(property="duration_min", type="integer", example=60),
 *     @OA\Property(property="focus_topic", type="string", nullable=true, example="Ansiedade e estresse no trabalho"),
 *     @OA\Property(property="session_notes", type="string", nullable=true, example="Paciente relatou melhora nos sintomas de ansiedade após as técnicas de respiração."),
 *     @OA\Property(property="type", type="string", enum={"In-person", "Online"}, example="In-person"),
 *     @OA\Property(property="meeting_url", type="string", format="uri", nullable=true, example="https://meet.google.com/abc-defg-hij"),
 *     @OA\Property(property="payment_status", type="string", enum={"Pending", "Paid"}, example="Pending"),
 *     @OA\Property(property="session_status", type="string", enum={"Scheduled", "Completed", "Canceled"}, example="Scheduled"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-08T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-08T12:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="EventRequest",
 *     title="Event Request",
 *     description="Request payload for creating/updating an event",
 *     oneOf={
 *         @OA\Schema(
 *             required={"client_id", "start_time", "duration_min", "type", "payment_status"},
 *             @OA\Property(property="client_id", type="integer", example=1)
 *         ),
 *         @OA\Schema(
 *             required={"client_ids", "start_time", "duration_min", "type", "payment_status"},
 *             @OA\Property(property="client_ids", type="array", @OA\Items(type="integer"), example={1,2,3})
 *         )
 *     },
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2025-07-08T14:00:00Z"),
 *     @OA\Property(property="duration_min", type="integer", example=60, minimum=1, maximum=1440),
 *     @OA\Property(property="focus_topic", type="string", nullable=true, example="Ansiedade e estresse no trabalho"),
 *     @OA\Property(property="session_notes", type="string", nullable=true, example="Paciente relatou melhora nos sintomas de ansiedade após as técnicas de respiração."),
 *     @OA\Property(property="type", type="string", enum={"In-person", "Online"}, example="In-person"),
 *     @OA\Property(property="payment_status", type="string", enum={"Pending", "Paid"}, example="Pending"),
 *     @OA\Property(property="session_status", type="string", enum={"Scheduled", "Completed", "Canceled"}, example="Scheduled")
 * )
 */
class EventResource extends JsonResource
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
            'participants' => $this->participants ? $this->participants->map(function($c) {
                return [
                    'id' => $c->id,
                    'full_name' => $c->full_name,
                    'email' => $c->email,
                ];
            }) : [],
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration_min' => $this->duration_min,
            'focus_topic' => $this->focus_topic,
            'session_notes' => $this->session_notes,
            'type' => $this->type,
            'meeting_url' => $this->meeting_url,
            'payment_status' => $this->payment_status,
            'session_status' => $this->session_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
