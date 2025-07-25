<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="UserModel",
 *     title="User",
 *     description="Modelo de Usuário",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="full_name", type="string", example="Maria Silva"),
 *     @OA\Property(property="professional_title", type="string", example="Psicóloga"),
 *     @OA\Property(property="email", type="string", example="maria@email.com"),
 *     @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
 *     @OA\Property(property="avatar_url", type="string", example="https://s3.amazonaws.com/bucket/avatar.png"),
 *     @OA\Property(property="specialty", type="string", example="Terapia Cognitivo-Comportamental"),
 *     @OA\Property(property="professional_license", type="string", example="CRP 12345/6"),
 *     @OA\Property(property="cpf_nif", type="string", example="123.456.789-00"),
 *     @OA\Property(property="office_address", type="string", example="Rua Exemplo, 123"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'professional_title',
        'email',
        'password',
        'phone',
        'avatar_url',
        'specialty',
        'professional_license',
        'cpf_nif',
        'office_address'
    ];

    protected $attributes = [
        'professional_title' => null,
        'phone' => null,
        'avatar_url' => null,
        'specialty' => null,
        'professional_license' => null,
        'cpf_nif' => null,
        'office_address' => null,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get the route key name for Laravel's route model binding.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    public function preference()
    {
        return $this->hasOne(UserPreference::class, 'user_id');
    }
}
