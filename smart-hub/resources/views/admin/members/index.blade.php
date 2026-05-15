@extends('layouts.app')
@section('title', 'Anggota')
@section('page-title', 'Manajemen Anggota')

@section('content')
<div class="page-header">
    <div><div class="page-title">Anggota</div><div class="page-subtitle">Kelola data anggota komunitas</div></div>
    <button class="btn btn-primary" onclick="openModal('modalAdd')"><i class="ti ti-user-plus"></i> Tambah Anggota</button>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" style="display:flex;gap:10px">
            <div class="search-bar"><i class="ti ti-search"></i><input type="text" name="search" class="form-control" placeholder="Cari nama / email..." value="{{ request('search') }}" style="width:240px"></div>
            <select name="role" class="form-control" style="width:120px" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                <option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admin</option>
                <option value="member" {{ request('role')=='member'?'selected':'' }}>Member</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-filter"></i></button>
        </form>
        <span class="page-info" style="margin-left:auto">{{ $members->total() }} anggota</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Anggota</th><th>No. Anggota</th><th>Role</th><th>Status</th><th>Booking</th><th>Checkout</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($members as $m)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="user-avatar">{{ strtoupper(substr($m->name,0,2)) }}</div>
                        <div>
                            <div class="fw-600">{{ $m->name }}</div>
                            <div class="text-sm text-muted">{{ $m->email }}</div>
                        </div>
                    </div>
                </td>
                <td><span class="font-mono text-sm">{{ $m->membership_number ?? '-' }}</span></td>
                <td><span class="badge {{ $m->role==='admin'?'purple':'blue' }}">{{ ucfirst($m->role) }}</span></td>
                <td><span class="badge {{ $m->is_active?'green':'red' }}">{{ $m->is_active?'Aktif':'Nonaktif' }}</span></td>
                <td class="fw-600">{{ $m->bookings_count }}</td>
                <td class="fw-600">{{ $m->checkouts_count }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.members.toggle', $m->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-secondary">{{ $m->is_active?'Nonaktifkan':'Aktifkan' }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><i class="ti ti-users-off"></i></div><div class="empty-title">Tidak ada anggota</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($members->hasPages())
    <div class="pagination">
        @if(!$members->onFirstPage())<a href="{{ $members->previousPageUrl() }}" class="page-btn"><i class="ti ti-chevron-left"></i></a>@endif
        @foreach($members->getUrlRange(max(1,$members->currentPage()-2),min($members->lastPage(),$members->currentPage()+2)) as $p=>$u)
            <a href="{{ $u }}" class="page-btn {{ $p==$members->currentPage()?'active':'' }}">{{ $p }}</a>
        @endforeach
        @if($members->hasMorePages())<a href="{{ $members->nextPageUrl() }}" class="page-btn"><i class="ti ti-chevron-right"></i></a>@endif
    </div>
    @endif
</div>

{{-- MODAL ADD MEMBER --}}
<div class="modal-backdrop" id="modalAdd" onclick="if(event.target===this)closeModal('modalAdd')">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Tambah Anggota</div>
            <button onclick="closeModal('modalAdd')" class="btn btn-secondary btn-sm btn-icon"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.members.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group"><label class="form-label">Nama Lengkap *</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label class="form-label">No. Telepon</label><input type="text" name="phone" class="form-control" placeholder="08xxx"></div>
                <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required minlength="8"></div>
                <div class="form-group mb-0">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalAdd')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ti ti-user-plus"></i> Tambah</button>
            </div>
        </form>
    </div>
</div>
@endsection
