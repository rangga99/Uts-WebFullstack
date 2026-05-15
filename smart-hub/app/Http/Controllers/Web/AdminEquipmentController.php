<?php

namespace App\Http\Controllers\Web;

// File: app/Http/Controllers/Web/AdminEquipmentController.php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminEquipmentController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
        // Call API: GET /api/v1/equipment
        $response  = $this->apiGet('/equipment', [
            'search'   => $request->search,
            'category' => $request->category,
            'status'   => $request->status,
            'page'     => $request->page ?? 1,
            'per_page' => 15,
        ]);

        $equipment = $this->makePaginator($response, 15, $request->url(), $request->query());

        return view('admin.equipment.index', compact('equipment'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'required|string|max:30',
            'category'       => ['required', Rule::in(['camera','audio','lighting','computer','other'])],
            'brand'          => 'nullable|string|max:80',
            'model'          => 'nullable|string|max:80',
            'serial_number'  => 'nullable|string|max:80',
            'condition'      => ['nullable', Rule::in(['excellent','good','fair','needs_repair'])],
            'location'       => 'nullable|string|max:100',
        ]);

        // Call API: POST /api/v1/admin/equipment
        $response = $this->apiPost('/admin/equipment', $data);

        if (isset($response['errors'])) {
            return back()->withErrors($response['errors'])->withInput();
        }
        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menambahkan peralatan.')->withInput();
        }

        return back()->with('success', 'Peralatan berhasil ditambahkan.');
    }

    public function update(Request $request, int $equipment)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:30',
            'category'  => ['required', Rule::in(['camera','audio','lighting','computer','other'])],
            'status'    => ['required', Rule::in(['available','checked_out','maintenance','retired'])],
            'condition' => ['required', Rule::in(['excellent','good','fair','needs_repair'])],
            'location'  => 'nullable|string|max:100',
        ]);

        // Call API: PUT /api/v1/admin/equipment/{id}
        $response = $this->apiPut("/admin/equipment/{$equipment}", $data);

        if (isset($response['errors'])) {
            return back()->withErrors($response['errors'])->withInput();
        }
        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal memperbarui peralatan.');
        }

        return back()->with('success', 'Peralatan berhasil diperbarui.');
    }

    public function destroy(int $equipment)
    {
        // Call API: DELETE /api/v1/admin/equipment/{id}
        $response = $this->apiDelete("/admin/equipment/{$equipment}");

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menghapus peralatan.');
        }

        return back()->with('success', 'Peralatan berhasil dihapus.');
    }
}
