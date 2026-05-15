<?php

namespace App\Models;

// File: app/Models/Booking.php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'user_id',
        'room_id',
        'start_datetime',
        'end_datetime',
        'duration_hours',
        'total_price',
        'status',
        'notes',
        'confirmed_by',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
        'confirmed_at'   => 'datetime',
        'cancelled_at'   => 'datetime',
        'duration_hours' => 'decimal:2',
        'total_price'    => 'decimal:2',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(EquipmentCheckout::class);
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'completed']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Generate unique booking code: BK-YYYYMMDD-XXX
     */
    public static function generateCode(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        return sprintf('BK-%s-%03d', $date, $count);
    }
}
