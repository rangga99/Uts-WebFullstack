<?php

namespace App\Http\Controllers\Web;

// File: app/Http/Controllers/Web/AdminManageControllers.php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// ─── BOOKINGS ────────────────────────────────────────────────────────────────
class AdminBookingController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
        // Call API: GET /api/v1/admin/bookings
        $response = $this->apiGet('/admin/bookings', [
            'search' => $request->search,
            'status' => $request->status,
            'date'   => $request->date,
            'page'   => $request->page ?? 1,
        ]);

        $bookings = $this->makePaginator($response, 20, $request->url(), $request->query());

        // Refresh sidebar pending badge
        $pending = collect($bookings->items())->where('status', 'pending')->count();
        if ($pending > 0 || ! $request->status) {
            session(['admin_pending_bookings' => $response['meta']['pending_count']
                ?? collect($response['data'] ?? [])->where('status', 'pending')->count()]);
        }

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(int $booking)
    {
        // Call API: GET /api/v1/bookings/{id}
        $response = $this->apiGet("/bookings/{$booking}");
        $booking  = $this->toObject($response['data'] ?? []);

        return view('admin.bookings.show', compact('booking'));
    }

    public function confirm(int $booking)
    {
        // Call API: PUT /api/v1/admin/bookings/{id}/confirm
        $response = $this->apiPut("/admin/bookings/{$booking}/confirm");

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal mengkonfirmasi booking.');
        }

        $code = $response['data']['booking_code'] ?? '';
        return back()->with('success', "Booking {$code} berhasil dikonfirmasi.");
    }

    public function cancel(int $booking)
    {
        // Call API: PUT /api/v1/admin/bookings/{id}/status
        $response = $this->apiPut("/admin/bookings/{$booking}/status", ['status' => 'cancelled']);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal membatalkan booking.');
        }

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}

// ─── ROOMS ────────────────────────────────────────────────────────────────────
class AdminRoomController extends Controller
{
    use ApiClient;

    public function index()
    {
        // Call API: GET /api/v1/rooms
        $response = $this->apiGet('/rooms', ['per_page' => 100]);
        $rooms    = $this->toObjects($response['data'] ?? []);

        return view('admin.rooms.index', compact('rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'required|string|max:20',
            'type'             => ['required', Rule::in(['workspace','studio','meeting'])],
            'capacity'         => 'required|integer|min:1',
            'price_per_hour'   => 'required|numeric|min:0',
            'facilities_input' => 'nullable|string',
        ]);

        $data['facilities'] = $data['facilities_input']
            ? array_map('trim', explode(',', $data['facilities_input']))
            : [];
        unset($data['facilities_input']);

        // Call API: POST /api/v1/admin/rooms
        $response = $this->apiPost('/admin/rooms', $data);

        if (isset($response['errors'])) {
            return back()->withErrors($response['errors'])->withInput();
        }
        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menambahkan ruangan.')->withInput();
        }

        return back()->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function update(Request $request, int $room)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'required|string|max:20',
            'type'             => ['required', Rule::in(['workspace','studio','meeting'])],
            'capacity'         => 'required|integer|min:1',
            'price_per_hour'   => 'required|numeric|min:0',
            'facilities_input' => 'nullable|string',
        ]);

        $data['facilities'] = $data['facilities_input']
            ? array_map('trim', explode(',', $data['facilities_input']))
            : [];
        unset($data['facilities_input']);

        // Call API: PUT /api/v1/admin/rooms/{id}
        $response = $this->apiPut("/admin/rooms/{$room}", $data);

        if (isset($response['errors'])) {
            return back()->withErrors($response['errors'])->withInput();
        }
        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal memperbarui ruangan.');
        }

        return back()->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(int $room)
    {
        // Call API: DELETE /api/v1/admin/rooms/{id}
        $response = $this->apiDelete("/admin/rooms/{$room}");

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menghapus ruangan.');
        }

        return back()->with('success', 'Ruangan berhasil dihapus.');
    }

    public function toggle(int $room)
    {
        // Fetch current state then flip is_available
        $current  = $this->apiGet("/rooms/{$room}");
        $roomData = $current['data'] ?? [];

        $response = $this->apiPut("/admin/rooms/{$room}", [
            'name'           => $roomData['name'],
            'code'           => $roomData['code'],
            'type'           => $roomData['type'],
            'capacity'       => $roomData['capacity'],
            'price_per_hour' => $roomData['price_per_hour'],
            'facilities'     => $roomData['facilities'] ?? [],
            'is_available'   => ! ($roomData['is_available'] ?? true),
        ]);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal mengubah status ruangan.');
        }

        return back()->with('success', 'Status ruangan berhasil diubah.');
    }
}

// ─── CHECKOUTS ────────────────────────────────────────────────────────────────
class AdminCheckoutController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
        // Call API: GET /api/v1/admin/equipment/checkouts
        $response  = $this->apiGet('/admin/equipment/checkouts', [
            'search' => $request->search,
            'status' => $request->status,
            'page'   => $request->page ?? 1,
        ]);

        $checkouts = $this->makePaginator($response, 20, $request->url(), $request->query());

        return view('admin.checkouts.index', compact('checkouts'));
    }

    public function processReturn(Request $request, int $checkout)
    {
        $request->validate([
            'condition_after' => ['required', Rule::in(['excellent','good','fair','needs_repair'])],
            'notes_return'    => 'nullable|string|max:500',
        ]);

        // Call API: POST /api/v1/equipment/checkouts/{id}/return
        $response = $this->apiPost("/equipment/checkouts/{$checkout}/return", [
            'condition_after' => $request->condition_after,
            'notes_return'    => $request->notes_return,
        ]);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal memproses pengembalian.');
        }

        return back()->with('success', 'Peralatan berhasil dicatat sebagai dikembalikan.');
    }
}

// ─── MEMBERS ─────────────────────────────────────────────────────────────────
class AdminMemberController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
        // Call API: GET /api/v1/admin/members
        $response = $this->apiGet('/admin/members', [
            'search' => $request->search,
            'role'   => $request->role,
            'page'   => $request->page ?? 1,
        ]);

        $members = $this->makePaginator($response, 20, $request->url(), $request->query());

        return view('admin.members.index', compact('members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in(['admin','member'])],
        ]);

        // Call API: POST /api/v1/admin/members
        $response = $this->apiPost('/admin/members', $request->only(
            'name', 'email', 'phone', 'password', 'role'
        ));

        if (isset($response['errors'])) {
            return back()->withErrors($response['errors'])->withInput();
        }
        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menambahkan anggota.')->withInput();
        }

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function toggle(int $user)
    {
        // Call API: PATCH /api/v1/admin/members/{id}/toggle
        $response = $this->apiPatch("/admin/members/{$user}/toggle");

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal mengubah status anggota.');
        }

        return back()->with('success', 'Status anggota berhasil diubah.');
    }
}
