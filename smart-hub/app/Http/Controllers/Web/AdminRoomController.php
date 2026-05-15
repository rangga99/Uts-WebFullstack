<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminRoomController extends Controller
{
    use ApiClient;

    public function index()
    {
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
        $response = $this->apiDelete("/admin/rooms/{$room}");

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menghapus ruangan.');
        }

        return back()->with('success', 'Ruangan berhasil dihapus.');
    }

    public function toggle(int $room)
    {
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
