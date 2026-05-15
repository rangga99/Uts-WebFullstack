@extends('layouts.app')
@section('title', 'Riwayat Saya')
@section('page-title', 'Riwayat Saya')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Riwayat Pinjaman</div>
        <div class="page-subtitle">Semua transaksi checkout peralatan Anda</div>
    </div>
</div>

{{-- STATUS TABS --}}
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    @foreach([''=> 'Semua', 'active'=>'Aktif', 'returned'=>'Dikembalikan', 'overdue'=>'Terlambat'] as $v=>$l)
        <a href="{{ route('member.checkouts.index', $v ? ['status'=>$v] : []) }}"
           class="filter-chip {{ request('status','') === $v ? 'active' : '' }}">{{ $l }}</a>
    @endforeach
</div>

@forelse($checkouts as $co)
@php $overdue = $co->status === 'active' && \Carbon\Carbon::parse($co->expected_return_at)->isPast(); @endphp
<div class="card" style="margin-bottom:14px;{{ $overdue ? 'border-color:#fca5a5' : '' }}">
    <div style="padding:18px 22px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
        {{-- ICON --}}
        <div style="width:44px;height:44px;border-radius:11px;flex-shrink:0;display:flex;align-items:center;justify-content:center;
            {{ $overdue ? 'background:var(--red-light)' : ($co->status==='returned' ? 'background:var(--green-light)' : 'background:var(--blue-light)') }}">
            <i class="ti ti-{{ $overdue ? 'alert-triangle' : ($co->status==='returned' ? 'package-import' : 'package-export') }}"
               style="font-size:20px;{{ $overdue ? 'color:var(--red)' : ($co->status==='returned' ? 'color:var(--green)' : 'color:var(--blue)') }}"></i>
        </div>

        {{-- INFO --}}
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;flex-wrap:wrap">
                <div style="font-size:15px;font-weight:700">{{ $co->equipment->name ?? '-' }}</div>
                @if($overdue)
                    <span class="badge red"><span class="badge-dot" style="background:currentColor"></span>Terlambat</span>
                @else
                    <span class="badge {{ ['active'=>'blue','returned'=>'green','lost'=>'gray'][$co->status]??'gray' }}">
                        <span class="badge-dot" style="background:currentColor"></span>{{ ucfirst($co->status === 'returned' ? 'Dikembalikan' : ($co->status === 'active' ? 'Aktif' : $co->status)) }}
                    </span>
                @endif
                <span class="font-mono text-sm text-muted">{{ $co->checkout_code }}</span>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap">
                <span class="text-sm text-muted"><i class="ti ti-package-export" style="font-size:12px;margin-right:3px"></i>Checkout: {{ \Carbon\Carbon::parse($co->checked_out_at)->format('d M Y, H:i') }}</span>
                <span class="text-sm {{ $overdue ? 'fw-600' : 'text-muted' }}" style="{{ $overdue ? 'color:var(--red)' : '' }}">
                    <i class="ti ti-clock" style="font-size:12px;margin-right:3px"></i>
                    Batas: {{ \Carbon\Carbon::parse($co->expected_return_at)->format('d M Y, H:i') }}
                    @if($overdue)({{ \Carbon\Carbon::parse($co->expected_return_at)->diffForHumans() }})@endif
                </span>
                @if($co->returned_at)
                    <span class="text-sm text-muted"><i class="ti ti-package-import" style="font-size:12px;margin-right:3px"></i>Kembali: {{ \Carbon\Carbon::parse($co->returned_at)->format('d M Y, H:i') }}</span>
                @endif
            </div>
        </div>

        {{-- CONDITION BADGES --}}
        <div style="text-align:right;flex-shrink:0">
            @php $cLabels=['excellent'=>'Sempurna','good'=>'Baik','fair'=>'Cukup','needs_repair'=>'Perlu Servis']; @endphp
            @php $cColors=['excellent'=>'green','good'=>'blue','fair'=>'amber','needs_repair'=>'red']; @endphp
            <div class="text-sm text-muted" style="margin-bottom:4px">Kondisi</div>
            <div style="display:flex;gap:6px;justify-content:flex-end">
                <span class="badge {{ $cColors[$co->condition_before]??'gray' }}" title="Sebelum">{{ $cLabels[$co->condition_before]??$co->condition_before }}</span>
                @if($co->condition_after)
                    <i class="ti ti-arrow-right" style="font-size:14px;color:var(--text-3);align-self:center"></i>
                    <span class="badge {{ $cColors[$co->condition_after]??'gray' }}" title="Sesudah">{{ $cLabels[$co->condition_after]??$co->condition_after }}</span>
                @endif
            </div>
        </div>

        {{-- RETURN BUTTON --}}
        @if($co->status === 'active')
        <button onclick="openReturnModal({{ $co->id }}, '{{ addslashes($co->equipment->name ?? '') }}')"
                class="btn btn-sm" style="background:var(--green-light);color:#065f46;border:1px solid #a7f3d0;flex-shrink:0">
            <i class="ti ti-package-import"></i> Kembalikan
        </button>
        @endif
    </div>

    @if($co->notes_checkout || $co->notes_return)
    <div style="padding:10px 22px 14px;border-top:1px solid var(--border);display:flex;gap:20px;flex-wrap:wrap">
        @if($co->notes_checkout)
            <span class="text-sm text-muted"><i class="ti ti-note" style="font-size:12px;margin-right:4px"></i>{{ $co->notes_checkout }}</span>
        @endif
        @if($co->notes_return)
            <span class="text-sm" style="color:var(--green)"><i class="ti ti-check" style="font-size:12px;margin-right:4px"></i>{{ $co->notes_return }}</span>
        @endif
    </div>
    @endif
</div>
@empty
<div class="card">
    <div class="empty-state">
        <div class="empty-icon"><i class="ti ti-package-off"></i></div>
        <div class="empty-title">Belum ada riwayat checkout</div>
        <div class="empty-desc">Pinjam peralatan untuk memulai</div>
        <a href="{{ route('member.equipment.index') }}" class="btn btn-primary" style="margin-top:16px"><i class="ti ti-camera"></i> Browse Peralatan</a>
    </div>
</div>
@endforelse

@if($checkouts->hasPages())
<div class="pagination" style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:12px 16px;margin-top:4px">
    @if(!$checkouts->onFirstPage())<a href="{{ $checkouts->previousPageUrl() }}" class="page-btn"><i class="ti ti-chevron-left"></i></a>@endif
    @foreach($checkouts->getUrlRange(max(1,$checkouts->currentPage()-2),min($checkouts->lastPage(),$checkouts->currentPage()+2)) as $p=>$u)
        <a href="{{ $u }}" class="page-btn {{ $p==$checkouts->currentPage()?'active':'' }}">{{ $p }}</a>
    @endforeach
    @if($checkouts->hasMorePages())<a href="{{ $checkouts->nextPageUrl() }}" class="page-btn"><i class="ti ti-chevron-right"></i></a>@endif
    <span class="page-info">{{ $checkouts->total() }} transaksi</span>
</div>
@endif

{{-- MODAL RETURN --}}
<div class="modal-backdrop" id="modalReturn" onclick="if(event.target===this)closeModal('modalReturn')">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Kembalikan Peralatan</div>
            <button onclick="closeModal('modalReturn')" class="btn btn-secondary btn-sm btn-icon"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="returnForm">
            @csrf
            <div class="modal-body">
                <div class="alert alert-info" style="margin-bottom:16px">
                    <i class="ti ti-info-circle"></i> Mengembalikan: <strong id="returnName"></strong>
                </div>
                <div class="form-group">
                    <label class="form-label">Kondisi Setelah Dipakai *</label>
                    <select name="condition_after" class="form-control" required>
                        <option value="excellent">Sempurna — tidak ada kerusakan</option>
                        <option value="good" selected>Baik — kondisi normal</option>
                        <option value="fair">Cukup — ada goresan kecil</option>
                        <option value="needs_repair">Perlu Perbaikan — ada kerusakan</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes_return" class="form-control" rows="3" placeholder="Kondisi peralatan saat dikembalikan..."></textarea>
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
    document.getElementById('returnForm').action = `/member/checkouts/${id}/return`;
    document.getElementById('returnName').textContent = name;
    openModal('modalReturn');
}
</script>
@endpush
@endsection
