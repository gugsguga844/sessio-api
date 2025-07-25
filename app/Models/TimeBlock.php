<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="TimeBlockModel",
 *     title="TimeBlock",
 *     description="Modelo de Bloco de Tempo",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Almoço"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2025-07-08T12:00:00Z"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2025-07-08T13:00:00Z"),
 *     @OA\Property(property="color_hex", type="string", example="#FF0000"),
 *     @OA\Property(property="emoji", type="string", example="🍔"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TimeBlock extends Model
{
    use HasFactory;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'start_time',
        'end_time',
        'color_hex',
        'emoji',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime:Y-m-d H:i:s',
        'end_time' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the time block.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include time blocks within a date range.
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->where('start_time', '>=', $start)
              ->where('start_time', '<=', $end);
        })->orWhere(function ($q) use ($start, $end) {
            $q->where('end_time', '>=', $start)
              ->where('end_time', '<=', $end);
        })->orWhere(function ($q) use ($start, $end) {
            $q->where('start_time', '<=', $start)
              ->where('end_time', '>=', $end);
        });
    }
}
