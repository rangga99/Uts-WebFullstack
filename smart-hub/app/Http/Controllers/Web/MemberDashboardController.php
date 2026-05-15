<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class MemberDashboardController extends Controller
{
    use ApiClient;

    public function index()
    {
        // GET /api/v1/auth/me — profile + active checkouts
        $meResponse      = $this->apiGet('/auth/me');
        $me              = $meResponse['data'] ?? [];
        $activeCheckouts = $this->toObjects($me['active_checkouts'] ?? []);

        // GET /api/v1/bookings/my — upcoming non-cancelled bookings
        $bookingsResponse = $this->apiGet('/bookings/my', ['per_page' => 20]);
        $allBookings      = $bookingsResponse['data'] ?? [];

        $upcomingBookings = collect($allBookings)
            ->filter(fn ($b) => ! in_array($b['status'], ['cancelled', 'completed'])
                              && strtotime($b['start_datetime']) >= time())
            ->take(5)
            ->pipe(fn ($c) => $this->toObjects($c->values()->all()));

        // GET /api/v1/equipment/checkouts/my — for total count
        $checkoutsResponse = $this->apiGet('/equipment/checkouts/my', ['per_page' => 1]);

        $totalBookings  = $bookingsResponse['meta']['total']  ?? count($allBookings);
        $totalCheckouts = $checkoutsResponse['meta']['total'] ?? count($checkoutsResponse['data'] ?? []);

        return view('member.dashboard', compact(
            'activeCheckouts',
            'upcomingBookings',
            'totalBookings',
            'totalCheckouts'
        ));
    }
}
