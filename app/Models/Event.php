<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    // Tipos de sessão
    public const TYPE_IN_PERSON = 'In-person';
    public const TYPE_ONLINE = 'Online';

    // Status de pagamento
    public const PAYMENT_PENDING = 'Pending';
    public const PAYMENT_PAID = 'Paid';

    // Status da sessão
    public const STATUS_SCHEDULED = 'Scheduled';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELED = 'Canceled';

    protected $fillable = [
        'user_id',
        'client_id',
        'start_time',
        'duration_min',
        'focus_topic',
        'session_notes',
        'type',
        'payment_status',
        'session_status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'duration_min' => 'integer',
    ];

    protected $attributes = [
        'payment_status' => self::PAYMENT_PENDING,
        'session_status' => self::STATUS_SCHEDULED,
    ];

    /**
     * Get the user (therapist) that owns the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->belongsToMany(Client::class, 'event_participants', 'event_id', 'client_id');
    }

    /**
     * Get the end time of the event.
     */
    public function getEndTimeAttribute()
    {
        return $this->start_time->copy()->addMinutes($this->duration_min);
    }

    /**
     * Check if the event is in the past.
     */
    public function isPast(): bool
    {
        return $this->end_time->isPast();
    }

    /**
     * Check if the event is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture();
    }

    /**
     * Check if the event is happening now.
     */
    public function isHappeningNow(): bool
    {
        $now = now();
        return $now->between($this->start_time, $this->end_time);
    }
}
