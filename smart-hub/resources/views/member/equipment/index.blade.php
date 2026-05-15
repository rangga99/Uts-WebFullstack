@extends('layouts.app')
@section('title', 'Pinjam Peralatan')
@section('page-title', 'Pinjam Peralatan')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Peralatan Tersedia</div>
        <div class="page-subtitle">Pilih peralatan yang ingin dipinjam</div>
    </div>
</div>

{{-- FILTER --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:14px 20px">
        <form method="GET">
            <div class="filter-bar">
                <div class="search-bar">
                    <i class="ti ti-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari peralatan..." value="{{ request('search') }}" style="width:220px">
                </div>
                @foreach([''=>'Semua','camera'=>'Kamera','audio'=>'Audio','lighting'=>'Lighting','computer'=>'Komputer','other'=>'Lainnya'] as $v=>$l)
                    <button type="submit" name="category" value="{{ $v }}" class="filter-chip {{ request('category')===$v?'active':'' }}">{{ $l }}</button>
                @endforeach
            </div>
        </form>
    </div>
</div>

{{-- EQUIPMENT GRID --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px">
@forelse($equipment as $item)
<div class="card" style="transition:all .18s" onmouseenter="this.style.boxShadow='0 8px 24px rgba(0,0,0,.1)';this.style.borderColor='var(--border-2)'" onmouseleave="this.style.boxShadow='';this.style.borderColor=''">
    <div style="padding:20px 20px 16px">
        {{-- TOP --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px">
            <div style="width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                {{ ['camera'=>'background:var(--blue-light)', 'audio'=>'background:var(--green-light)', 'lighting'=>'background:var(--amber-light)', 'computer'=>'background:var(--purple-light)', 'other'=>'background:var(--bg)'][$item->category] ?? 'background:var(--bg)' }}">
                <i class="ti ti-{{ ['camera'=>'camera','audio'=>'microphone','lighting'=>'bulb','computer'=>'device-laptop','other'=>'tool'][$item->category] ?? 'tool' }}"
                   style="font-size:20px;{{ ['camera'=>'color:var(--blue)','audio'=>'color:var(--green)','lighting'=>'color:var(--amber)','computer'=>'color:var(--purple)','other'=>'color:var(--text-3)'][$item->category] ?? 'color:var(--text-3)' }}"></i>
            </div>
            <span class="badge {{ $item->status === 'available' ? 'green' : ($item->status === 'checked_out' ? 'blue' : 'amber') }}">
                <span class="badge-dot" style="background:currentColor"></span>
                {{ ['available'=>'Tersedia','checked_out'=>'Dipinjam','maintenance'=>'Maintenance','retired'=>'Pensiun'][$item->status] ?? $item->status }}
            </span>
        </div>

        {{-- INFO --}}
        <div style="font-size:15px;font-weight:700;color:var(--text-1);margin-bottom:3px">{{ $item->name }}</div>
        @if($item->brand)
        <div style="font-size:12.5px;color:var(--text-3)">{{ $item->brand }} {{ $item->model }}</div>
        @endif

        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px">
            <span class="font-mono text-sm" style="background:var(--bg);padding:3px 8px;border-radius:5px">{{ $item->code }}</span>
            <span class="badge {{ ['excellent'=>'green','good'=>'blue','fair'=>'amber','needs_repair'=>'red'][$item->condition]??'gray' }}">
                {{ ['excellent'=>'Sempurna','good'=>'Baik','fair'=>'Cukup','needs_repair'=>'Perlu Servis'][$item->condition] ?? $item->condition }}
            </span>
        </div>
    </div>

    <div style="padding:14px 20px;border-top:1px solid var(--border)">
        <div class="text-sm text-muted" style="margin-bottom:10px"><i class="ti ti-map-pin" style="font-size:13px;margin-right:4px"></i>{{ $item->location }}</div>
        @if($item->status === 'available')
            <button class="btn btn-primary" style="width:100%;justify-content:center"
                onclick="openCheckoutModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->code }}')">
                <i class="ti ti-package-export"></i> Pinjam Sekarang
            </button>
        @elseif($item->status === 'checked_out')
            @php $co = data_get($item, 'activeCheckout'); @endphp
            <div class="alert alert-info" style="margin:0;font-size:12.5px;padding:8px 12px">
                <i class="ti ti-clock" style="font-size:13px"></i>
                Kembali: {{ $co ? \Carbon\Carbon::parse($co->expected_return_at)->format('d M, H:i') : 'N/A' }}
            </div>
        @else
            <button class="btn btn-secondary" style="width:100%;justify-content:center;opacity:.6" disabled>
                <i class="ti ti-ban"></i> Tidak Tersedia
            </button>
        @endif
    </div>
</div>
@empty
<div class="card" style="grid-column:1/-1">
    <div class="empty-state">
        <div class="empty-icon"><i class="ti ti-camera-off"></i></div>
        <div class="empty-title">Tidak ada peralatan</div>
        <div class="empty-desc">Belum ada peralatan yang tersedia saat ini</div>
    </div>
</div>
@endforelse
</div>

{{-- PAGINATION --}}
@if($equipment->hasPages())
<div style="display:flex;justify-content:center;margin-top:24px">
    <div class="d-flex gap-8">
        @if(!$equipment->onFirstPage())<a href="{{ $equipment->previousPageUrl() }}" class="page-btn"><i class="ti ti-chevron-left"></i></a>@endif
        @foreach($equipment->getUrlRange(max(1,$equipment->currentPage()-2),min($equipment->lastPage(),$equipment->currentPage()+2)) as $p=>$u)
            <a href="{{ $u }}" class="page-btn {{ $p==$equipment->currentPage()?'active':'' }}">{{ $p }}</a>
        @endforeach
        @if($equipment->hasMorePages())<a href="{{ $equipment->nextPageUrl() }}" class="page-btn"><i class="ti ti-chevron-right"></i></a>@endif
    </div>
</div>
@endif

{{-- MODAL CHECKOUT --}}
<div class="modal-backdrop" id="modalCheckout" onclick="if(event.target===this)closeModal('modalCheckout')">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Pinjam Peralatan</div>
            <button onclick="closeModal('modalCheckout')" class="btn btn-secondary btn-sm btn-icon"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="checkoutForm">
            @csrf
            <div class="modal-body">
                <div style="background:var(--blue-light);border:1px solid var(--blue-mid);border-radius:var(--radius-sm);padding:14px 16px;margin-bottom:20px">
                    <div style="font-size:13px;color:var(--blue);font-weight:600;margin-bottom:2px" id="modalEquipmentCode"></div>
                    <div style="font-size:15px;font-weight:700;color:var(--text-1)" id="modalEquipmentName"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal & Waktu Pengembalian *</label>
                    <input type="datetime-local" name="expected_return_at" id="returnAt" class="form-control" required min="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
                    <div class="form-hint">Minimal 1 jam dari sekarang</div>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Keperluan / Catatan</label>
                    <textarea name="notes_checkout" class="form-control" rows="3" placeholder="Untuk keperluan apa peralatan ini dipinjam..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalCheckout')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ti ti-package-export"></i> Konfirmasi Pinjam</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openCheckoutModal(id, name, code) {
    document.getElementById('checkoutForm').action = `/member/equipment/${id}/checkout`;
    document.getElementById('modalEquipmentName').textContent = name;
    document.getElementById('modalEquipmentCode').textContent = code;
    // Set default return time = now + 4 hours
    const d = new Date(); d.setHours(d.getHours() + 4);
    document.getElementById('returnAt').value = d.toISOString().slice(0,16);
    openModal('modalCheckout');
}
</script>
@endpush
@endsection
