@extends('layouts.app')
@section('title', 'Checkout Peralatan')
@section('page-title', 'Manajemen Checkout')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Checkout Peralatan</div>
        <div class="page-subtitle">Semua transaksi peminjaman peralatan</div>
    </div>
    <div class="d-flex gap-8">
        @php $overdue = $checkouts->where('status','active')->filter(fn($c)=>\Carbon\Carbon::parse($c->expected_return_at)->isPast())->count(); @endphp
        @if($overdue > 0)
        <span class="badge red" style="font-size:13px;padding:7px 12px"><i class="ti ti-alert-triangle"></i> {{ $overdue }} Terlambat</span>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:14px 20px">
        <form method="GET">
            <div class="filter-bar">
                <div class="search-bar"><i class="ti ti-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Kode / nama..." value="{{ request('search') }}" style="width:200px">
                </div>
                <select name="status" class="form-control" style="width:130px" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option>
                    <option value="returned" {{ request('status')=='returned'?'selected':'' }}>Dikembalikan</option>
                    <option value="overdue" {{ request('status')=='overdue'?'selected':'' }}>Terlambat</option>
                    <option value="lost" {{ request('status')=='lost'?'selected':'' }}>Hilang</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-filter"></i> Filter</button>
                @if(request()->hasAny(['search','status']))<a href="{{ route('admin.checkouts.index') }}" class="btn btn-secondary btn-sm"><i class="ti ti-x"></i> Reset</a>@endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Kode</th><th>Peralatan</th><th>Peminjam</th><th>Checkout</th><th>Batas Kembali</th><th>Kembali</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            @forelse($checkouts as $co)
            @php $isOverdue = $co->status==='active' && \Carbon\Carbon::parse($co->expected_return_at)->isPast(); @endphp
            <tr style="{{ $isOverdue ? 'background:var(--red-light)' : '' }}">
                <td><span class="font-mono text-sm" style="background:var(--bg);padding:3px 8px;border-radius:5px">{{ $co->checkout_code }}</span></td>
                <td>
                    <div class="fw-600">{{ $co->equipment->name ?? '-' }}</div>
                    <div class="text-sm text-muted">{{ $co->equipment->code ?? '' }}</div>
                </td>
                <td>
                    <div class="fw-600">{{ $co->user->name ?? '-' }}</div>
                    <div class="text-sm text-muted">{{ $co->user->phone ?? '' }}</div>
                </td>
                <td class="text-sm">{{ \Carbon\Carbon::parse($co->checked_out_at)->format('d M Y H:i') }}</td>
                <td>
                    <div class="text-sm {{ $isOverdue ? 'fw-600' : '' }}" style="{{ $isOverdue ? 'color:var(--red)' : '' }}">
                        {{ \Carbon\Carbon::parse($co->expected_return_at)->format('d M Y H:i') }}
                    </div>
                    @if($isOverdue)<div class="text-sm" style="color:var(--red)">{{ \Carbon\Carbon::parse($co->expected_return_at)->diffForHumans() }}</div>@endif
                </td>
                <td class="text-sm">{{ $co->returned_at ? \Carbon\Carbon::parse($co->returned_at)->format('d M Y H:i') : '—' }}</td>
                <td>
                    @php $sc=$isOverdue?'red':(['active'=>'blue','returned'=>'green','overdue'=>'red','lost'=>'gray'][$co->status]??'gray'); @endphp
                    <span class="badge {{ $sc }}">{{ $isOverdue ? 'Terlambat' : ucfirst($co->status) }}</span>
                </td>
                <td>
                    @if($co->status === 'active')
                    <button onclick="openReturnModal({{ $co->id }}, '{{ addslashes($co->equipment->name ?? '') }}')" class="btn btn-sm" style="background:var(--green-light);color:#065f46;border:1px solid #a7f3d0">
                        <i class="ti ti-package-import"></i> Proses Kembali
                    </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8"><div class="empty-state"><div class="empty-icon"><i class="ti ti-package-off"></i></div><div class="empty-title">Tidak ada data checkout</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($checkouts->hasPages())
    <div class="pagination">
        @if(!$checkouts->onFirstPage())<a href="{{ $checkouts->previousPageUrl() }}" class="page-btn"><i class="ti ti-chevron-left"></i></a>@endif
        @foreach($checkouts->getUrlRange(max(1,$checkouts->currentPage()-2),min($checkouts->lastPage(),$checkouts->currentPage()+2)) as $p=>$u)
            <a href="{{ $u }}" class="page-btn {{ $p==$checkouts->currentPage()?'active':'' }}">{{ $p }}</a>
        @endforeach
        @if($checkouts->hasMorePages())<a href="{{ $checkouts->nextPageUrl() }}" class="page-btn"><i class="ti ti-chevron-right"></i></a>@endif
        <span class="page-info">{{ $checkouts->total() }} total transaksi</span>
    </div>
    @endif
</div>

{{-- MODAL RETURN --}}
<div class="modal-backdrop" id="modalReturn" onclick="if(event.target===this)closeModal('modalReturn')">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Proses Pengembalian</div>
            <button onclick="closeModal('modalReturn')" class="btn btn-secondary btn-sm btn-icon"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="returnForm">
            @csrf
            <div class="modal-body">
                <div class="alert alert-info" style="margin-bottom:16px"><i class="ti ti-info-circle"></i> Memproses pengembalian: <strong id="returnEquipmentName"></strong></div>
                <div class="form-group">
                    <label class="form-label">Kondisi Peralatan Setelah Dikembalikan *</label>
                    <select name="condition_after" class="form-control" required>
                        <option value="excellent">Sempurna — tidak ada kerusakan</option>
                        <option value="good" selected>Baik — kondisi normal</option>
                        <option value="fair">Cukup — ada goresan kecil</option>
                        <option value="needs_repair">Perlu Perbaikan — ada kerusakan</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Catatan Pengembalian</label>
                    <textarea name="notes_return" class="form-control" rows="3" placeholder="Kondisi saat dikembalikan, catatan khusus..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalReturn')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ti ti-package-import"></i> Konfirmasi Kembali</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openReturnModal(id, name) {
    document.getElementById('returnForm').action = `/admin/checkouts/${id}/return`;
    document.getElementById('returnEquipmentName').textContent = name;
    openModal('modalReturn');
}
</script>
@endpush
@endsection
