<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
        $response = $this->apiGet('/admin/bookings', [
            'search' => $request->search,
            'status' => $request->status,
            'date'   => $request->date,
            'page'   => $request->page ?? 1,
        ]);

        $bookings = $this->makePaginator($response, 20, $request->url(), $request->query());

        // Refresh sidebar pending badge
        session(['admin_pending_bookings' =>
            collect($response['data'] ?? [])->where('status', 'pending')->count()
        ]);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(int $booking)
    {
        $response = $this->apiGet("/bookings/{$booking}");
        $booking  = $this->toObject($response['data'] ?? []);

        return view('admin.bookings.show', compact('booking'));
    }

    public function confirm(int $booking)
    {
        $response = $this->apiPut("/admin/bookings/{$booking}/confirm");

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal mengkonfirmasi booking.');
        }

        $code = $response['data']['booking_code'] ?? '';
        return back()->with('success', "Booking {$code} berhasil dikonfirmasi.");
    }

    public function cancel(int $booking)
    {
        $response = $this->apiPut("/admin/bookings/{$booking}/status", ['status' => 'cancelled']);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal membatalkan booking.');
        }

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}
