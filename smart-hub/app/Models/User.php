<?php

namespace App\Models;

// File: app/Models/User.php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'membership_number',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // -------------------------------------------------------
    // Helpers / Accessors
    // -------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(EquipmentCheckout::class);
    }

    public function confirmedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'confirmed_by');
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMembers($query)
    {
        return $query->where('role', 'member');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }
}
