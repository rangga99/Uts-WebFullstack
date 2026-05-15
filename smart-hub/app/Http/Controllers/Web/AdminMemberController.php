<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminMemberController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
        $response = $this->apiGet('/admin/members', [
            'search' => $request->search,
            'role'   => $request->role,
            'page'   => $request->page ?? 1,
        ]);

        $members = $this->makePaginator($response, 20, $request->url(), $request->query());

        return view('admin.members.index', compact('members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in(['admin','member'])],
        ]);

        $response = $this->apiPost('/admin/members', $request->only(
            'name', 'email', 'phone', 'password', 'role'
        ));

        if (isset($response['errors'])) {
            return back()->withErrors($response['errors'])->withInput();
        }
        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menambahkan anggota.')->withInput();
        }

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function toggle(int $user)
    {
        $response = $this->apiPatch("/admin/members/{$user}/toggle");

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal mengubah status anggota.');
        }

        return back()->with('success', 'Status anggota berhasil diubah.');
    }
}
