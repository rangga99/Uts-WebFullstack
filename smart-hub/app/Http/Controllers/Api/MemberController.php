<?php

namespace App\Http\Controllers\Api;

// File: app/Http/Controllers/Api/MemberController.php

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    /**
     * GET /api/v1/admin/members
     * List all users with booking/checkout counts.
     */
    public function index(Request $request): JsonResponse
    {
        $members = User::withCount(['bookings', 'checkouts'])
            ->when($request->search, fn ($q) => $q->where(function ($s) use ($request) {
                $s->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->when($request->role, fn ($q) => $q->where('role', $request->role))
            ->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'message' => 'Data anggota berhasil diambil',
            'data'    => $members->items(),
            'meta'    => [
                'current_page' => $members->currentPage(),
                'last_page'    => $members->lastPage(),
                'per_page'     => $members->perPage(),
                'total'        => $members->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/members
     * Create a new user.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in(['admin','member'])],
        ]);

        $count = User::members()->count() + 1;
        $user  = User::create([
            ...$data,
            'password'          => Hash::make($data['password']),
            'membership_number' => $data['role'] === 'member'
                ? sprintf('MBR-%s-%03d', now()->year, $count)
                : null,
            'is_active'         => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Anggota berhasil ditambahkan',
            'data'    => $user,
        ], 201);
    }

    /**
     * PATCH /api/v1/admin/members/{user}/toggle
     * Toggle user active status.
     */
    public function toggle(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menonaktifkan akun sendiri.',
            ], 422);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Status anggota berhasil diubah',
            'data'    => $user->fresh(),
        ]);
    }
}
