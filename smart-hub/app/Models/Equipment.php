<?php

namespace App\Models;

// File: app/Models/Equipment.php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'code',
        'category',
        'brand',
        'model',
        'serial_number',
        'condition',
        'status',
        'description',
        'purchase_date',
        'purchase_price',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date'  => 'date',
            'purchase_price' => 'decimal:2',
        ];
    }

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function checkouts(): HasMany
    {
        return $this->hasMany(EquipmentCheckout::class);
    }

    public function activeCheckout()
    {
        return $this->hasOne(EquipmentCheckout::class)
                    ->where('status', 'active');
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function markAsCheckedOut(): void
    {
        $this->update(['status' => 'checked_out']);
    }

    public function markAsAvailable(): void
    {
        $this->update(['status' => 'available']);
    }
}
