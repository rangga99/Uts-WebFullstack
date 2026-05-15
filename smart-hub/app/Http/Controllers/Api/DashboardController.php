<?php

namespace App\Http\Controllers\Api;

// File: app/Http/Controllers/Api/DashboardController.php

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\EquipmentCheckout;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/admin/dashboard/stats
     */
    public function stats(): JsonResponse
    {
        $today = today();

        // Equipment stats
        $equipmentStats = Equipment::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = 'checked_out' THEN 1 ELSE 0 END) as checked_out,
                SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance
            ")
            ->first();

        // Booking stats
        $bookingStats = Booking::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as ongoing
            ")
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->first();

        // Today's bookings
        $todayBookings = Booking::with('user:id,name', 'room:id,name')
            ->whereDate('start_datetime', $today)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_datetime')
            ->get(['id', 'booking_code', 'user_id', 'room_id', 'start_datetime', 'end_datetime', 'status']);

        // Overdue checkouts
        $overdueCheckouts = EquipmentCheckout::with([
            'user:id,name,phone',
            'equipment:id,name,code',
        ])
        ->overdue()
        ->get(['id', 'checkout_code', 'user_id', 'equipment_id', 'expected_return_at']);

        // Active members count
        $activeMembersCount = User::members()->active()->count();

        return response()->json([
            'success' => true,
            'message' => 'Statistik dashboard berhasil diambil',
            'data'    => [
                'equipment' => [
                    'total'        => (int) $equipmentStats->total,
                    'available'    => (int) $equipmentStats->available,
                    'checked_out'  => (int) $equipmentStats->checked_out,
                    'maintenance'  => (int) $equipmentStats->maintenance,
                ],
                'bookings_30d' => [
                    'total'     => (int) $bookingStats->total,
                    'pending'   => (int) $bookingStats->pending,
                    'confirmed' => (int) $bookingStats->confirmed,
                    'ongoing'   => (int) $bookingStats->ongoing,
                ],
                'today_bookings'    => $todayBookings,
                'overdue_checkouts' => $overdueCheckouts,
                'active_members'    => $activeMembersCount,
                'rooms_total'       => Room::count(),
                'rooms_available'   => Room::available()->count(),
                'generated_at'      => now()->toDateTimeString(),
            ],
        ]);
    }
}
