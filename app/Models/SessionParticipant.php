<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="SessionParticipantModel",
 *     title="SessionParticipant",
 *     description="Participante de Sessão (pivot de sessão em grupo)",
 *     @OA\Property(property="session_id", type="integer", example=1),
 *     @OA\Property(property="client_id", type="integer", example=2)
 * )
 */
class SessionParticipant extends Model
{
    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'session_id',
        'client_id',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
} 