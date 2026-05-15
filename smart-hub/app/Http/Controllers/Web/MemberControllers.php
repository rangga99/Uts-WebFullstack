<?php

namespace App\Http\Controllers\Web;

// File: app/Http/Controllers/Web/MemberControllers.php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// ─── MEMBER DASHBOARD ────────────────────────────────────────────────────────
class MemberDashboardController extends Controller
{
    use ApiClient;

    public function index()
    {
        // GET /api/v1/auth/me — profile + active checkouts
        $meResponse      = $this->apiGet('/auth/me');
        $me              = $meResponse['data'] ?? [];
        $activeCheckouts = $this->toObjects($me['active_checkouts'] ?? []);

        // GET /api/v1/bookings/my — upcoming, non-cancelled bookings
        $bookingsResponse = $this->apiGet('/bookings/my', ['per_page' => 20]);
        $allBookings      = $bookingsResponse['data'] ?? [];

        $upcomingBookings = collect($allBookings)
            ->filter(fn ($b) => ! in_array($b['status'], ['cancelled', 'completed'])
                              && strtotime($b['start_datetime']) >= time())
            ->take(5)
            ->pipe(fn ($c) => $this->toObjects($c->values()->all()));

        // GET /api/v1/equipment/checkouts/my — total count
        $checkoutsResponse = $this->apiGet('/equipment/checkouts/my', ['per_page' => 1]);
        $totalCheckouts    = $checkoutsResponse['meta']['total'] ?? count($checkoutsResponse['data'] ?? []);
        $totalBookings     = $bookingsResponse['meta']['total']  ?? count($allBookings);

        return view('member.dashboard', compact(
            'activeCheckouts',
            'upcomingBookings',
            'totalBookings',
            'totalCheckouts'
        ));
    }
}

// ─── MEMBER EQUIPMENT ────────────────────────────────────────────────────────
class MemberEquipmentController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
        // GET /api/v1/equipment
        $response  = $this->apiGet('/equipment', [
            'search'   => $request->search,
            'category' => $request->category,
            'page'     => $request->page ?? 1,
            'per_page' => 12,
        ]);

        $equipment = $this->makePaginator($response, 12, $request->url(), $request->query());

        return view('member.equipment.index', compact('equipment'));
    }

    public function checkout(Request $request, int $equipment)
    {
        $request->validate([
            'expected_return_at' => 'required|date|after:now',
            'notes_checkout'     => 'nullable|string|max:500',
        ]);

        // POST /api/v1/equipment/{id}/checkout
        $response = $this->apiPost("/equipment/{$equipment}/checkout", [
            'expected_return_at' => $request->expected_return_at,
            'notes_checkout'     => $request->notes_checkout,
        ]);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal melakukan checkout.');
        }

        $name = $response['data']['equipment']['name'] ?? 'Peralatan';
        return redirect()->route('member.checkouts.index')
            ->with('success', "Peralatan {$name} berhasil dipinjam!");
    }

    public function returnEquipment(Request $request, int $checkout)
    {
        $request->validate([
            'condition_after' => ['required', Rule::in(['excellent','good','fair','needs_repair'])],
            'notes_return'    => 'nullable|string|max:500',
        ]);

        // POST /api/v1/equipment/checkouts/{id}/return
        $response = $this->apiPost("/equipment/checkouts/{$checkout}/return", [
            'condition_after' => $request->condition_after,
            'notes_return'    => $request->notes_return,
        ]);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal mengembalikan peralatan.');
        }

        return back()->with('success', 'Peralatan berhasil dikembalikan. Terima kasih!');
    }
}

// ─── MEMBER BOOKINGS ─────────────────────────────────────────────────────────
class MemberBookingController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
        // GET /api/v1/bookings/my
        $response = $this->apiGet('/bookings/my', [
            'status'   => $request->status,
            'page'     => $request->page ?? 1,
            'per_page' => 10,
        ]);

        $bookings = $this->makePaginator($response, 10, $request->url(), $request->query());

        return view('member.bookings.index', compact('bookings'));
    }

    public function create()
    {
        // GET /api/v1/rooms (available rooms only)
        $response = $this->apiGet('/rooms', ['per_page' => 100]);
        $rooms    = $this->toObjects($response['data'] ?? []);

        return view('member.bookings.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_id'        => 'required|integer',
            'start_datetime' => 'required|date|after:now',
            'end_datetime'   => 'required|date|after:start_datetime',
            'notes'          => 'nullable|string|max:500',
        ]);

        // POST /api/v1/bookings
        $response = $this->apiPost('/bookings', [
            'room_id'        => $request->room_id,
            'start_datetime' => $request->start_datetime,
            'end_datetime'   => $request->end_datetime,
            'notes'          => $request->notes,
        ]);

        if (!($response['success'] ?? false)) {
            if (isset($response['errors'])) {
                return back()->withInput()->withErrors($response['errors']);
            }
            return back()->withInput()->with('error', $response['message'] ?? 'Gagal membuat booking.');
        }

        return redirect()->route('member.bookings.index')
            ->with('success', 'Booking berhasil dikirim! Menunggu konfirmasi admin.');
    }

    public function cancel(Request $request, int $booking)
    {
        // POST /api/v1/bookings/{id}/cancel
        $response = $this->apiPost("/bookings/{$booking}/cancel");

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Booking tidak dapat dibatalkan.');
        }

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}

// ─── MEMBER CHECKOUTS ────────────────────────────────────────────────────────
class MemberCheckoutController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
        // GET /api/v1/equipment/checkouts/my
        $response  = $this->apiGet('/equipment/checkouts/my', [
            'status'   => $request->status,
            'page'     => $request->page ?? 1,
            'per_page' => 10,
        ]);

        $checkouts = $this->makePaginator($response, 10, $request->url(), $request->query());

        return view('member.checkouts.index', compact('checkouts'));
    }
}
