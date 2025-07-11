<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Client",
 *     title="Client",
 *     description="Client model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="full_name", type="string", example="João Silva"),
 *     @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+5511999999999"),
 *     @OA\Property(property="birth_date", type="string", format="date", nullable=true, example="1990-01-01"),
 *     @OA\Property(property="cpf_nif", type="string", nullable=true, example="123.456.789-00"),
 *     @OA\Property(property="emergency_contact", type="string", nullable=true, example="Maria Silva - (11) 99999-8888"),
 *     @OA\Property(property="case_summary", type="string", nullable=true, example="Paciente em acompanhamento desde 2024, apresentando melhora progressiva."),
 *     @OA\Property(property="status", type="string", example="Active", enum={"Active", "Inactive"}),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-08T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-08T12:00:00Z")
 * )
 */
class ClientResource extends JsonResource
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
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date?->toDateString(),
            'cpf_nif' => $this->cpf_nif,
            'emergency_contact' => $this->emergency_contact,
            'case_summary' => $this->case_summary,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
