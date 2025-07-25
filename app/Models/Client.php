<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="ClientModel",
 *     title="Client",
 *     description="Modelo de Cliente",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="full_name", type="string", example="Maria Silva"),
 *     @OA\Property(property="email", type="string", example="maria@email.com"),
 *     @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
 *     @OA\Property(property="cpf_nif", type="string", example="123.456.789-00"),
 *     @OA\Property(property="emergency_contact", type="string", example="João - (11) 98888-8888"),
 *     @OA\Property(property="case_summary", type="string", example="Paciente com histórico de ansiedade."),
 *     @OA\Property(property="status", type="string", example="Ativo"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'birth_date',
        'cpf_nif',
        'emergency_contact',
        'case_summary',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'Active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação: um cliente pode ter várias sessões individuais
     */
    public function sessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Session::class);
    }
    /**
     * Relação: um cliente pode participar de várias sessões em grupo
     */
    public function groupSessions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Session::class, 'session_participants', 'client_id', 'session_id');
    }
}
