<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="UserPreferenceModel",
 *     title="UserPreference",
 *     description="Preferências do Usuário",
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="client_terminology", type="string", example="Cliente"),
 *     @OA\Property(property="default_calendar_view", type="string", example="Weekly"),
 *     @OA\Property(property="visible_calendar_days", type="integer", example=5),
 *     @OA\Property(property="show_canceled_sessions", type="boolean", example=false),
 *     @OA\Property(property="interface_theme", type="string", example="Light"),
 *     @OA\Property(property="language", type="string", example="pt-BR"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-08T12:00:00Z")
 * )
 */
class UserPreference extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $fillable = [
        'user_id',
        'client_terminology',
        'default_calendar_view',
        'visible_calendar_days',
        'show_canceled_sessions',
        'interface_theme',
        'language',
        'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
} 