@extends('layouts.app')
@section('title', 'Booking Ruangan')
@section('page-title', 'Manajemen Booking')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Booking Ruangan</div>
        <div class="page-subtitle">Kelola semua pemesanan ruangan</div>
    </div>
</div>

{{-- QUICK STATS --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:22px">
    @foreach([['Pending','pending','amber','clock'],['Terkonfirmasi','confirmed','blue','circle-check'],['Berlangsung','ongoing','green','player-play'],['Selesai','completed','gray','check'],['Dibatalkan','cancelled','red','x']] as [$label,$status,$color,$icon])
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:14px 16px;cursor:pointer" onclick="filterStatus('{{ $status }}')">
        <div style="font-size:20px;font-weight:700;color:var(--{{ $color }})">{{ collect($bookings->items())->where('status',$status)->count() }}</div>
        <div style="font-size:12px;color:var(--text-3);font-weight:500;margin-top:3px">{{ $label }}</div>
    </div>
    @endforeach
</div>

{{-- FILTER --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:14px 20px">
        <form method="GET" id="filterForm">
            <div class="filter-bar">
                <div class="search-bar">
                    <i class="ti ti-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Kode / nama anggota..." value="{{ request('search') }}" style="width:220px">
                </div>
                <select name="status" id="statusFilter" class="form-control" style="width:150px" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    @foreach(['pending'=>'Pending','confirmed'=>'Terkonfirmasi','ongoing'=>'Berlangsung','completed'=>'Selesai','cancelled'=>'Dibatalkan'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <input type="date" name="date" class="form-control" style="width:150px" value="{{ request('date') }}" onchange="this.form.submit()">
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-filter"></i> Filter</button>
                @if(request()->hasAny(['search','status','date']))
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm"><i class="ti ti-x"></i> Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- TABLE --}}
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Anggota</th>
                    <th>Ruangan</th>
                    <th>Waktu</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($bookings as $booking)
            <tr>
                <td><span class="font-mono text-sm" style="background:var(--bg);padding:3px 8px;border-radius:5px">{{ $booking->booking_code }}</span></td>
                <td>
                    <div class="fw-600">{{ $booking->user->name ?? '-' }}</div>
                    <div class="text-sm text-muted">{{ $booking->user->membership_number ?? '' }}</div>
                </td>
                <td>
                    <div class="fw-600">{{ $booking->room->name ?? '-' }}</div>
                    <div class="text-sm text-muted">{{ ucfirst($booking->room->type ?? '') }}</div>
                </td>
                <td>
                    <div class="text-sm fw-600">{{ \Carbon\Carbon::parse($booking->start_datetime)->format('d M Y') }}</div>
                    <div class="text-sm text-muted font-mono">{{ \Carbon\Carbon::parse($booking->start_datetime)->format('H:i') }} – {{ \Carbon\Carbon::parse($booking->end_datetime)->format('H:i') }}</div>
                </td>
                <td class="fw-600">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                <td>
                    @php $sc=['pending'=>'amber','confirmed'=>'blue','ongoing'=>'green','completed'=>'gray','cancelled'=>'red'][$booking->status]??'gray'; @endphp
                    <span class="badge {{ $sc }}"><span class="badge-dot" style="background:currentColor"></span>{{ ucfirst($booking->status) }}</span>
                </td>
                <td style="text-align:right">
                    <div class="d-flex gap-8" style="justify-content:flex-end">
                        @if($booking->status === 'pending')
                        <form method="POST" action="{{ route('admin.bookings.confirm', $booking->id) }}">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-sm" style="background:var(--green-light);color:#065f46;border:1px solid #a7f3d0" title="Konfirmasi">
                                <i class="ti ti-check"></i> Konfirmasi
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-secondary btn-sm btn-icon" title="Detail">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><i class="ti ti-calendar-off"></i></div><div class="empty-title">Tidak ada booking</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($bookings->hasPages())
    <div class="pagination">
        @if(!$bookings->onFirstPage())<a href="{{ $bookings->previousPageUrl() }}" class="page-btn"><i class="ti ti-chevron-left"></i></a>@endif
        @foreach($bookings->getUrlRange(max(1,$bookings->currentPage()-2),min($bookings->lastPage(),$bookings->currentPage()+2)) as $p=>$u)
            <a href="{{ $u }}" class="page-btn {{ $p==$bookings->currentPage()?'active':'' }}">{{ $p }}</a>
        @endforeach
        @if($bookings->hasMorePages())<a href="{{ $bookings->nextPageUrl() }}" class="page-btn"><i class="ti ti-chevron-right"></i></a>@endif
        <span class="page-info">{{ $bookings->total() }} total booking</span>
    </div>
    @endif
</div>

@push('scripts')
<script>
function filterStatus(status) {
    document.getElementById('statusFilter').value = status;
    document.getElementById('filterForm').submit();
}
</script>
@endpush
@endsection
