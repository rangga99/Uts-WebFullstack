<?php

namespace App\Http\Controllers\Api;

// File: app/Http/Controllers/Api/BookingController.php

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * POST /api/v1/bookings
     * Create a new room booking.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_id'        => 'required|exists:rooms,id',
            'start_datetime' => 'required|date|after:now',
            'end_datetime'   => 'required|date|after:start_datetime',
            'notes'          => 'nullable|string|max:500',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if (! $room->isAvailableFor($validated['start_datetime'], $validated['end_datetime'])) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak tersedia pada waktu yang dipilih.',
            ], 409);
        }

        $start = now()->parse($validated['start_datetime']);
        $end   = now()->parse($validated['end_datetime']);
        $hours = $start->diffInMinutes($end) / 60;

        $booking = Booking::create([
            'booking_code'   => Booking::generateCode(),
            'user_id'        => $request->user()->id,
            'room_id'        => $validated['room_id'],
            'start_datetime' => $validated['start_datetime'],
            'end_datetime'   => $validated['end_datetime'],
            'duration_hours' => $hours,
            'total_price'    => $room->price_per_hour * $hours,
            'status'         => 'pending',
            'notes'          => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibuat, menunggu konfirmasi admin',
            'data'    => $booking->load('room:id,name,code,type'),
        ], 201);
    }

    /**
     * GET /api/v1/bookings/my
     * Current user's bookings.
     */
    public function myBookings(Request $request): JsonResponse
    {
        $bookings = Booking::with('room:id,name,code,type')
            ->where('user_id', $request->user()->id)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('start_datetime')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Data booking berhasil diambil',
            'data'    => $bookings->items(),
            'meta'    => ['total' => $bookings->total()],
        ]);
    }

    /**
     * GET /api/v1/bookings/{booking}
     * Single booking detail (owner or admin).
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        if ($request->user()->id !== $booking->user_id && ! $request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses tidak diizinkan.',
            ], 403);
        }

        $booking->load(['room', 'user:id,name,email', 'confirmedBy:id,name', 'checkouts.equipment:id,name,code']);

        return response()->json([
            'success' => true,
            'message' => 'Detail booking berhasil diambil',
            'data'    => $booking,
        ]);
    }

    /**
     * POST /api/v1/bookings/{booking}/cancel
     * Cancel a booking.
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'cancellation_reason' => 'nullable|string|max:255',
        ]);

        if ($request->user()->id !== $booking->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses tidak diizinkan.'], 403);
        }

        if (! $booking->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Booking dengan status "' . $booking->status . '" tidak dapat dibatalkan.',
            ], 409);
        }

        $booking->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibatalkan',
            'data'    => ['booking_code' => $booking->booking_code, 'status' => 'cancelled'],
        ]);
    }

    // -------------------------------------------------------
    // ADMIN ENDPOINTS
    // -------------------------------------------------------

    /**
     * GET /api/v1/admin/bookings
     * All bookings (admin).
     */
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['user:id,name,membership_number', 'room:id,name,code'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->room_id, fn ($q) => $q->where('room_id', $request->room_id))
            ->when($request->date, fn ($q) => $q->whereDate('start_datetime', $request->date))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Data booking berhasil diambil',
            'data'    => $bookings->items(),
            'meta'    => [
                'current_page' => $bookings->currentPage(),
                'total'        => $bookings->total(),
            ],
        ]);
    }

    /**
     * PUT /api/v1/admin/bookings/{booking}/confirm
     * Confirm a pending booking.
     */
    public function confirm(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya booking berstatus pending yang dapat dikonfirmasi.',
            ], 409);
        }

        $booking->update([
            'status'       => 'confirmed',
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dikonfirmasi',
            'data'    => $booking->fresh(['room:id,name', 'user:id,name']),
        ]);
    }

    /**
     * PUT /api/v1/admin/bookings/{booking}/status
     * Update booking status (admin).
     */
    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:confirmed,ongoing,completed,cancelled',
        ]);

        $booking->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status booking berhasil diperbarui',
            'data'    => ['booking_code' => $booking->booking_code, 'status' => $request->status],
        ]);
    }
}
