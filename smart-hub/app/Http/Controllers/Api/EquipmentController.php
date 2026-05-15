<?php

namespace App\Http\Controllers\Api;

// File: app/Http/Controllers/Api/EquipmentController.php

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    // -------------------------------------------------------
    // PUBLIC / MEMBER ENDPOINTS
    // -------------------------------------------------------

    /**
     * GET /api/v1/equipment
     * List available equipment (tablet-friendly).
     */
    public function index(Request $request): JsonResponse
    {
        $equipment = Equipment::with('activeCheckout.user:id,name,membership_number')
            ->when($request->category, fn ($q) => $q->ofCategory($request->category))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            }))
            ->select(['id', 'name', 'code', 'category', 'brand', 'model', 'status', 'condition', 'location'])
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'message' => 'Data peralatan berhasil diambil',
            'data'    => $equipment->items(),
            'meta'    => [
                'current_page' => $equipment->currentPage(),
                'last_page'    => $equipment->lastPage(),
                'per_page'     => $equipment->perPage(),
                'total'        => $equipment->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/equipment/{id}
     * Get equipment detail.
     */
    public function show(Equipment $equipment): JsonResponse
    {
        $equipment->load('activeCheckout.user:id,name,membership_number');

        return response()->json([
            'success' => true,
            'message' => 'Detail peralatan berhasil diambil',
            'data'    => $equipment,
        ]);
    }

    /**
     * POST /api/v1/equipment/{equipment}/checkout
     * Member checks out equipment via tablet.
     */
    public function checkout(Request $request, Equipment $equipment): JsonResponse
    {
        $request->validate([
            'expected_return_at' => 'required|date|after:now',
            'booking_id'         => 'nullable|exists:bookings,id',
            'notes_checkout'     => 'nullable|string|max:500',
        ]);

        // Race condition guard: use DB transaction + row lock
        return DB::transaction(function () use ($request, $equipment) {
            // Lock the equipment row
            $equipment = Equipment::lockForUpdate()->findOrFail($equipment->id);

            if (! $equipment->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Peralatan tidak tersedia untuk di-checkout. Status: ' . $equipment->status,
                ], 409);
            }

            $checkout = EquipmentCheckout::create([
                'checkout_code'      => EquipmentCheckout::generateCode(),
                'user_id'            => $request->user()->id,
                'equipment_id'       => $equipment->id,
                'booking_id'         => $request->booking_id,
                'checked_out_at'     => now(),
                'expected_return_at' => $request->expected_return_at,
                'status'             => 'active',
                'condition_before'   => $equipment->condition,
                'notes_checkout'     => $request->notes_checkout,
                'processed_by'       => $request->user()->id,
            ]);

            $equipment->markAsCheckedOut();

            return response()->json([
                'success' => true,
                'message' => 'Peralatan berhasil di-checkout',
                'data'    => [
                    'checkout_code'      => $checkout->checkout_code,
                    'equipment'          => [
                        'id'   => $equipment->id,
                        'name' => $equipment->name,
                        'code' => $equipment->code,
                    ],
                    'checked_out_at'     => $checkout->checked_out_at->toDateTimeString(),
                    'expected_return_at' => $checkout->expected_return_at->toDateTimeString(),
                    'status'             => $checkout->status,
                ],
            ], 201);
        });
    }

    /**
     * POST /api/v1/equipment/checkouts/{checkout}/return
     * Member returns equipment.
     */
    public function returnEquipment(Request $request, EquipmentCheckout $checkout): JsonResponse
    {
        $request->validate([
            'condition_after' => ['required', Rule::in(['excellent', 'good', 'fair', 'needs_repair'])],
            'notes_return'    => 'nullable|string|max:500',
        ]);

        // Only owner or admin can return
        if ($request->user()->id !== $checkout->user_id && ! $request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak melakukan pengembalian ini.',
            ], 403);
        }

        if ($checkout->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Checkout ini sudah tidak aktif.',
            ], 409);
        }

        DB::transaction(function () use ($request, $checkout) {
            $checkout->update([
                'returned_at'     => now(),
                'status'          => 'returned',
                'condition_after' => $request->condition_after,
                'notes_return'    => $request->notes_return,
            ]);

            // Update equipment status and condition
            $newStatus = $request->condition_after === 'needs_repair' ? 'maintenance' : 'available';
            $checkout->equipment->update([
                'status'    => $newStatus,
                'condition' => $request->condition_after,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil dikembalikan',
            'data'    => [
                'checkout_code'  => $checkout->checkout_code,
                'returned_at'    => now()->toDateTimeString(),
                'condition_after' => $request->condition_after,
            ],
        ]);
    }

    /**
     * GET /api/v1/equipment/checkouts/my
     * Get current user's checkout history.
     */
    public function myCheckouts(Request $request): JsonResponse
    {
        $checkouts = EquipmentCheckout::with('equipment:id,name,code,category')
            ->where('user_id', $request->user()->id)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('checked_out_at')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat checkout berhasil diambil',
            'data'    => $checkouts->items(),
            'meta'    => [
                'current_page' => $checkouts->currentPage(),
                'total'        => $checkouts->total(),
            ],
        ]);
    }

    // -------------------------------------------------------
    // ADMIN ENDPOINTS
    // -------------------------------------------------------

    /**
     * POST /api/v1/admin/equipment
     * Create new equipment (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'required|string|max:30|unique:equipment,code',
            'category'       => ['required', Rule::in(['camera', 'audio', 'lighting', 'computer', 'other'])],
            'brand'          => 'nullable|string|max:80',
            'model'          => 'nullable|string|max:80',
            'serial_number'  => 'nullable|string|max:80|unique:equipment,serial_number',
            'condition'      => ['nullable', Rule::in(['excellent', 'good', 'fair', 'needs_repair'])],
            'description'    => 'nullable|string',
            'purchase_date'  => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'location'       => 'nullable|string|max:100',
        ]);

        $equipment = Equipment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil ditambahkan',
            'data'    => $equipment,
        ], 201);
    }

    /**
     * PUT /api/v1/admin/equipment/{id}
     * Update equipment (admin only).
     */
    public function update(Request $request, Equipment $equipment): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:100',
            'code'           => ['sometimes', 'string', 'max:30', Rule::unique('equipment')->ignore($equipment->id)],
            'category'       => ['sometimes', Rule::in(['camera', 'audio', 'lighting', 'computer', 'other'])],
            'brand'          => 'nullable|string|max:80',
            'model'          => 'nullable|string|max:80',
            'serial_number'  => ['nullable', 'string', 'max:80', Rule::unique('equipment')->ignore($equipment->id)],
            'condition'      => ['sometimes', Rule::in(['excellent', 'good', 'fair', 'needs_repair'])],
            'status'         => ['sometimes', Rule::in(['available', 'checked_out', 'maintenance', 'retired'])],
            'description'    => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'location'       => 'nullable|string|max:100',
        ]);

        $equipment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil diperbarui',
            'data'    => $equipment->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/admin/equipment/{id}
     * Delete equipment (admin only, only if not checked out).
     */
    public function destroy(Equipment $equipment): JsonResponse
    {
        if ($equipment->status === 'checked_out') {
            return response()->json([
                'success' => false,
                'message' => 'Peralatan tidak dapat dihapus karena sedang dipinjam.',
            ], 409);
        }

        $equipment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil dihapus',
        ]);
    }

    /**
     * GET /api/v1/admin/equipment/checkouts
     * All checkouts (admin view).
     */
    public function allCheckouts(Request $request): JsonResponse
    {
        $checkouts = EquipmentCheckout::with([
            'user:id,name,membership_number',
            'equipment:id,name,code',
            'processedBy:id,name',
        ])
        ->when($request->status, fn ($q) => $q->where('status', $request->status))
        ->when($request->overdue, fn ($q) => $q->overdue())
        ->orderByDesc('checked_out_at')
        ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Data checkout berhasil diambil',
            'data'    => $checkouts->items(),
            'meta'    => [
                'current_page' => $checkouts->currentPage(),
                'total'        => $checkouts->total(),
            ],
        ]);
    }
}
