<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="full_name", type="string", example="Maria Silva"),
 *     @OA\Property(property="professional_title", type="string", nullable=true, example="Dra."),
 *     @OA\Property(property="email", type="string", format="email", example="maria@email.com"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+5511999999999"),
 *     @OA\Property(property="avatar_url", type="string", format="uri", nullable=true, example="https://example.com/avatar.jpg"),
 *     @OA\Property(property="specialty", type="string", nullable=true, example="Psicóloga Clínica"),
 *     @OA\Property(property="professional_license", type="string", nullable=true, example="CRP 06/123456"),
 *     @OA\Property(property="cpf_nif", type="string", nullable=true, example="123.456.789-00"),
 *     @OA\Property(property="office_address", type="string", nullable=true, example="Rua Exemplo, 123 - São Paulo/SP"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2025-07-08T12:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-08T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-08T12:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class UserResource extends JsonResource
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
            'full_name' => $this->full_name,
            'professional_title' => $this->professional_title,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url,
            'specialty' => $this->specialty,
            'professional_license' => $this->professional_license,
            'cpf_nif' => $this->cpf_nif,
            'office_address' => $this->office_address,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->whenNotNull($this->deleted_at),
        ];
    }
}
