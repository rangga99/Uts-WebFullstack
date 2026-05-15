<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCheckoutController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
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
