<?php

namespace App\Models;

// File: app/Models/EquipmentCheckout.php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentCheckout extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkout_code',
        'user_id',
        'equipment_id',
        'booking_id',
        'checked_out_at',
        'expected_return_at',
        'returned_at',
        'status',
        'condition_before',
        'condition_after',
        'notes_checkout',
        'notes_return',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'checked_out_at'     => 'datetime',
            'expected_return_at' => 'datetime',
            'returned_at'        => 'datetime',
        ];
    }

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
                     ->where('expected_return_at', '<', now());
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    public function isOverdue(): bool
    {
        return $this->status === 'active'
            && $this->expected_return_at->isPast();
    }

    /**
     * Generate unique checkout code: CO-YYYYMMDD-XXX
     */
    public static function generateCode(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        return sprintf('CO-%s-%03d', $date, $count);
    }
}
