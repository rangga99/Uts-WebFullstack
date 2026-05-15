<?php

namespace App\Models;

// File: app/Models/Room.php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'capacity',
        'description',
        'facilities',
        'price_per_hour',
        'is_available',
    ];

    protected $casts = [
        'facilities'     => 'array',
        'is_available'   => 'boolean',
        'price_per_hour' => 'decimal:2',
        'capacity'       => 'integer',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    /**
     * Check if room is available for a given time slot.
     */
    public function isAvailableFor(string $start, string $end, ?int $excludeBookingId = null): bool
    {
        if (! $this->is_available) {
            return false;
        }

        $query = $this->bookings()
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                  ->orWhereBetween('end_datetime', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_datetime', '<=', $start)
                         ->where('end_datetime', '>=', $end);
                  });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count() === 0;
    }
}
