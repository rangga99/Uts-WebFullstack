<?php

namespace App\Http\Controllers\Api;

// File: app/Http/Controllers/Api/RoomController.php

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /**
     * GET /api/v1/rooms
     */
    public function index(Request $request): JsonResponse
    {
        $rooms = Room::available()
            ->when($request->type, fn ($q) => $q->ofType($request->type))
            ->when($request->min_capacity, fn ($q) => $q->where('capacity', '>=', $request->min_capacity))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type', 'capacity', 'facilities', 'price_per_hour', 'is_available']);

        return response()->json([
            'success' => true,
            'message' => 'Data ruangan berhasil diambil',
            'data'    => $rooms,
        ]);
    }

    /**
     * GET /api/v1/rooms/{room}
     */
    public function show(Room $room): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail ruangan berhasil diambil',
            'data'    => $room,
        ]);
    }

    /**
     * GET /api/v1/rooms/{room}/availability?date=2025-05-12
     */
    public function availability(Request $request, Room $room): JsonResponse
    {
        $request->validate([
            'date'  => 'required|date|after_or_equal:today',
            'start' => 'nullable|date_format:H:i',
            'end'   => 'nullable|date_format:H:i|after:start',
        ]);

        $date = $request->date;

        $bookings = $room->bookings()
            ->whereDate('start_datetime', $date)
            ->whereNotIn('status', ['cancelled'])
            ->get(['id', 'booking_code', 'start_datetime', 'end_datetime', 'status']);

        $isAvailable = $request->start
            ? $room->isAvailableFor("$date {$request->start}", "$date {$request->end}")
            : $room->is_available;

        return response()->json([
            'success' => true,
            'message' => 'Data ketersediaan ruangan berhasil diambil',
            'data'    => [
                'room'         => ['id' => $room->id, 'name' => $room->name, 'code' => $room->code],
                'date'         => $date,
                'is_available' => $isAvailable,
                'bookings'     => $bookings,
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/rooms
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'required|string|max:20|unique:rooms,code',
            'type'           => ['required', Rule::in(['workspace', 'studio', 'meeting'])],
            'capacity'       => 'required|integer|min:1|max:100',
            'description'    => 'nullable|string',
            'facilities'     => 'nullable|array',
            'facilities.*'   => 'string|max:50',
            'price_per_hour' => 'required|numeric|min:0',
        ]);

        $room = Room::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ruangan berhasil ditambahkan',
            'data'    => $room,
        ], 201);
    }

    /**
     * PUT /api/v1/admin/rooms/{room}
     */
    public function update(Request $request, Room $room): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:100',
            'type'           => ['sometimes', Rule::in(['workspace', 'studio', 'meeting'])],
            'capacity'       => 'sometimes|integer|min:1|max:100',
            'description'    => 'nullable|string',
            'facilities'     => 'nullable|array',
            'price_per_hour' => 'sometimes|numeric|min:0',
            'is_available'   => 'sometimes|boolean',
        ]);

        $room->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ruangan berhasil diperbarui',
            'data'    => $room->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/admin/rooms/{room}
     */
    public function destroy(Room $room): JsonResponse
    {
        $hasActiveBookings = $room->bookings()
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->exists();

        if ($hasActiveBookings) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak dapat dihapus karena memiliki booking aktif.',
            ], 409);
        }

        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ruangan berhasil dihapus',
        ]);
    }
}
