<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MemberBookingController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
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
        $response = $this->apiPost("/bookings/{$booking}/cancel");

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Booking tidak dapat dibatalkan.');
        }

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}
