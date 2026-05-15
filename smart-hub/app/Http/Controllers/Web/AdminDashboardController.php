<?php

namespace App\Http\Controllers\Web;

// File: app/Http/Controllers/Web/AdminDashboardController.php

use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    use ApiClient;

    public function index()
    {
        // Call API: GET /api/v1/admin/dashboard/stats
        $response = $this->apiGet('/admin/dashboard/stats');
        $raw      = $response['data'] ?? [];

        $stats = [
            'equipment' => array_merge(
                ['total' => 0, 'available' => 0, 'checked_out' => 0, 'maintenance' => 0],
                (array) ($raw['equipment'] ?? [])
            ),
            'bookings_30d' => array_merge(
                ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'ongoing' => 0],
                (array) ($raw['bookings_30d'] ?? [])
            ),
            'today_bookings' => $this->toObjects($raw['today_bookings'] ?? []),
            'overdue_checkouts' => $this->toObjects($raw['overdue_checkouts'] ?? []),
            'active_members' => $raw['active_members'] ?? 0,
            'rooms_total' => $raw['rooms_total'] ?? 0,
            'rooms_available' => $raw['rooms_available'] ?? 0,
        ];

        // Cache pending booking count so the sidebar badge doesn't need a DB query
        session(['admin_pending_bookings' => $stats['bookings_30d']['pending'] ?? 0]);

        return view('admin.dashboard', compact('stats'));
    }
}
