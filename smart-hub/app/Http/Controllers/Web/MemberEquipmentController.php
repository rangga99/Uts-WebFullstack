<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberEquipmentController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
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
