@extends('layouts.app')
@section('title', 'Booking Saya')
@section('page-title', 'Booking Saya')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Booking Saya</div>
        <div class="page-subtitle">Daftar semua pemesanan ruangan Anda</div>
    </div>
    <a href="{{ route('member.bookings.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Booking Baru</a>
</div>

{{-- STATUS TABS --}}
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    @foreach(['' => 'Semua', 'pending' => 'Pending', 'confirmed' => 'Terkonfirmasi', 'ongoing' => 'Berlangsung', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $v => $l)
        <a href="{{ route('member.bookings.index', $v ? ['status' => $v] : []) }}"
           class="filter-chip {{ request('status', '') === $v ? 'active' : '' }}">{{ $l }}</a>
    @endforeach
</div>

@forelse($bookings as $booking)
<div class="card" style="margin-bottom:14px;transition:all .18s" onmouseenter="this.style.boxShadow='0 4px 16px rgba(0,0,0,.08)'" onmouseleave="this.style.boxShadow=''">
    <div style="padding:18px 22px;display:flex;align-items:center;gap:18px;flex-wrap:wrap">
        {{-- ICON --}}
        <div style="width:46px;height:46px;border-radius:11px;background:{{ ['workspace'=>'var(--blue-light)','studio'=>'var(--purple-light)','meeting'=>'var(--green-light)'][$booking->room->type??'workspace'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="ti ti-{{ ['workspace'=>'device-desktop','studio'=>'camera','meeting'=>'users'][$booking->room->type??'workspace'] }}"
               style="font-size:20px;color:{{ ['workspace'=>'var(--blue)','studio'=>'var(--purple)','meeting'=>'var(--green)'][$booking->room->type??'workspace'] }}"></i>
        </div>
        {{-- INFO --}}
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;flex-wrap:wrap">
                <div style="font-size:15px;font-weight:700">{{ $booking->room->name ?? '-' }}</div>
                @php $sc=['pending'=>'amber','confirmed'=>'blue','ongoing'=>'green','completed'=>'gray','cancelled'=>'red'][$booking->status]??'gray'; @endphp
                <span class="badge {{ $sc }}"><span class="badge-dot" style="background:currentColor"></span>{{ ucfirst($booking->status) }}</span>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap">
                <span class="text-sm text-muted"><i class="ti ti-calendar" style="font-size:12px;margin-right:3px"></i>{{ \Carbon\Carbon::parse($booking->start_datetime)->isoFormat('ddd, D MMM Y') }}</span>
                <span class="text-sm text-muted font-mono"><i class="ti ti-clock" style="font-size:12px;margin-right:3px"></i>{{ \Carbon\Carbon::parse($booking->start_datetime)->format('H:i') }} – {{ \Carbon\Carbon::parse($booking->end_datetime)->format('H:i') }}</span>
                <span class="text-sm text-muted"><i class="ti ti-hourglass" style="font-size:12px;margin-right:3px"></i>{{ $booking->duration_hours }} jam</span>
            </div>
        </div>
        {{-- PRICE --}}
        <div style="text-align:right;flex-shrink:0">
            <div style="font-size:16px;font-weight:700;color:var(--blue)">Rp {{ number_format($booking->total_price,0,',','.') }}</div>
            <div class="text-sm text-muted font-mono">{{ $booking->booking_code }}</div>
        </div>
        {{-- ACTIONS --}}
        <div style="display:flex;gap:8px;flex-shrink:0">
            @if(in_array($booking->status, ['pending','confirmed']))
            <form method="POST" action="{{ route('member.bookings.cancel', $booking->id) }}" onsubmit="return confirm('Batalkan booking ini?')">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm"><i class="ti ti-x"></i> Batalkan</button>
            </form>
            @endif
        </div>
    </div>
    @if($booking->notes)
    <div style="padding:10px 22px 14px;border-top:1px solid var(--border)">
        <span class="text-sm text-muted"><i class="ti ti-note" style="font-size:12px;margin-right:4px"></i>{{ $booking->notes }}</span>
    </div>
    @endif
</div>
@empty
<div class="card">
    <div class="empty-state">
        <div class="empty-icon"><i class="ti ti-calendar-off"></i></div>
        <div class="empty-title">Belum ada booking</div>
        <div class="empty-desc">Pesan ruangan untuk mulai bekerja</div>
        <a href="{{ route('member.bookings.create') }}" class="btn btn-primary" style="margin-top:16px"><i class="ti ti-plus"></i> Booking Sekarang</a>
    </div>
</div>
@endforelse

@if($bookings->hasPages())
<div class="pagination" style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:12px 16px;margin-top:4px">
    @if(!$bookings->onFirstPage())<a href="{{ $bookings->previousPageUrl() }}" class="page-btn"><i class="ti ti-chevron-left"></i></a>@endif
    @foreach($bookings->getUrlRange(max(1,$bookings->currentPage()-2),min($bookings->lastPage(),$bookings->currentPage()+2)) as $p=>$u)
        <a href="{{ $u }}" class="page-btn {{ $p==$bookings->currentPage()?'active':'' }}">{{ $p }}</a>
    @endforeach
    @if($bookings->hasMorePages())<a href="{{ $bookings->nextPageUrl() }}" class="page-btn"><i class="ti ti-chevron-right"></i></a>@endif
    <span class="page-info">{{ $bookings->total() }} booking</span>
</div>
@endif
@endsection
