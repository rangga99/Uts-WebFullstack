@extends('layouts.app')
@section('title', 'Beranda Member')
@section('page-title', 'Beranda')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Halo, {{ explode(' ', auth()->user()->name)[0] }} </div>
        <div class="page-subtitle">Selamat datang di Smart-Hub — {{ now()->isoFormat('dddd, D MMMM Y') }}</div>
    </div>
</div>

{{-- QUICK ACTIONS --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px">
    <a href="{{ route('member.equipment.index') }}" style="text-decoration:none">
        <div class="card" style="padding:22px;cursor:pointer;transition:all .18s" onmouseenter="this.style.borderColor='var(--blue)';this.style.boxShadow='0 6px 20px rgba(37,99,235,.12)'" onmouseleave="this.style.borderColor='';this.style.boxShadow=''">
            <div style="width:44px;height:44px;background:var(--blue-light);border-radius:11px;display:flex;align-items:center;justify-content:center;margin-bottom:14px">
                <i class="ti ti-camera" style="font-size:22px;color:var(--blue)"></i>
            </div>
            <div style="font-size:15px;font-weight:700;color:var(--text-1)">Pinjam Peralatan</div>
            <div style="font-size:13px;color:var(--text-3);margin-top:4px">Browse & checkout alat studio</div>
        </div>
    </a>
    <a href="{{ route('member.bookings.create') }}" style="text-decoration:none">
        <div class="card" style="padding:22px;cursor:pointer;transition:all .18s" onmouseenter="this.style.borderColor='var(--green)';this.style.boxShadow='0 6px 20px rgba(5,150,105,.12)'" onmouseleave="this.style.borderColor='';this.style.boxShadow=''">
            <div style="width:44px;height:44px;background:var(--green-light);border-radius:11px;display:flex;align-items:center;justify-content:center;margin-bottom:14px">
                <i class="ti ti-calendar-plus" style="font-size:22px;color:var(--green)"></i>
            </div>
            <div style="font-size:15px;font-weight:700;color:var(--text-1)">Booking Ruangan</div>
            <div style="font-size:13px;color:var(--text-3);margin-top:4px">Pesan studio atau workspace</div>
        </div>
    </a>
    <a href="{{ route('member.checkouts.index') }}" style="text-decoration:none">
        <div class="card" style="padding:22px;cursor:pointer;transition:all .18s" onmouseenter="this.style.borderColor='var(--purple)';this.style.boxShadow='0 6px 20px rgba(124,58,237,.12)'" onmouseleave="this.style.borderColor='';this.style.boxShadow=''">
            <div style="width:44px;height:44px;background:var(--purple-light);border-radius:11px;display:flex;align-items:center;justify-content:center;margin-bottom:14px">
                <i class="ti ti-history" style="font-size:22px;color:var(--purple)"></i>
            </div>
            <div style="font-size:15px;font-weight:700;color:var(--text-1)">Riwayat Saya</div>
            <div style="font-size:13px;color:var(--text-3);margin-top:4px">Booking & checkout aktif</div>
        </div>
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    {{-- ACTIVE CHECKOUTS --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="ti ti-package-export" style="font-size:15px;vertical-align:-2px;margin-right:6px;color:var(--blue)"></i>Pinjaman Aktif</div>
            <a href="{{ route('member.checkouts.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        @if($activeCheckouts->count() > 0)
        <div class="table-wrap">
            <table>
                <thead><tr><th>Peralatan</th><th>Batas Kembali</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($activeCheckouts as $co)
                @php $overdue = \Carbon\Carbon::parse($co->expected_return_at)->isPast(); @endphp
                <tr>
                    <td>
                        <div class="fw-600">{{ $co->equipment->name ?? '-' }}</div>
                        <div class="text-sm text-muted font-mono">{{ $co->equipment->code ?? '' }}</div>
                    </td>
                    <td>
                        <div class="text-sm {{ $overdue ? 'fw-600' : '' }}" style="{{ $overdue ? 'color:var(--red)' : '' }}">
                            {{ \Carbon\Carbon::parse($co->expected_return_at)->format('d M, H:i') }}
                        </div>
                        @if($overdue)
                            <div class="text-sm" style="color:var(--red)">{{ \Carbon\Carbon::parse($co->expected_return_at)->diffForHumans() }}</div>
                        @else
                            <div class="text-sm text-muted">{{ \Carbon\Carbon::parse($co->expected_return_at)->diffForHumans() }}</div>
                        @endif
                    </td>
                    <td><span class="badge {{ $overdue ? 'red' : 'blue' }}">{{ $overdue ? 'Terlambat' : 'Aktif' }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:32px">
            <div class="empty-icon" style="font-size:36px"><i class="ti ti-package-off"></i></div>
            <div class="empty-title">Tidak ada pinjaman aktif</div>
            <div class="empty-desc">Browse peralatan dan lakukan checkout</div>
        </div>
        @endif
    </div>

    {{-- UPCOMING BOOKINGS --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="ti ti-calendar" style="font-size:15px;vertical-align:-2px;margin-right:6px;color:var(--green)"></i>Booking Mendatang</div>
            <a href="{{ route('member.bookings.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        @if($upcomingBookings->count() > 0)
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ruangan</th><th>Jadwal</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($upcomingBookings as $b)
                <tr>
                    <td>
                        <div class="fw-600">{{ $b->room->name ?? '-' }}</div>
                        <div class="text-sm text-muted">{{ number_format($b->duration_hours, 1) }} jam</div>
                    </td>
                    <td>
                        <div class="text-sm fw-600">{{ \Carbon\Carbon::parse($b->start_datetime)->format('d M Y') }}</div>
                        <div class="text-sm text-muted font-mono">{{ \Carbon\Carbon::parse($b->start_datetime)->format('H:i') }}</div>
                    </td>
                    <td>
                        @php $sc=['pending'=>'amber','confirmed'=>'blue','ongoing'=>'green'][$b->status]??'gray'; @endphp
                        <span class="badge {{ $sc }}">{{ ucfirst($b->status) }}</span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:32px">
            <div class="empty-icon" style="font-size:36px"><i class="ti ti-calendar-off"></i></div>
            <div class="empty-title">Tidak ada booking mendatang</div>
            <div class="empty-desc">Buat booking ruangan sekarang</div>
        </div>
        @endif
    </div>

</div>

{{-- MEMBER PROFILE CARD --}}
<div class="card" style="margin-top:20px">
    <div class="card-body" style="display:flex;align-items:center;gap:20px">
        <div class="user-avatar" style="width:54px;height:54px;font-size:18px;flex-shrink:0">
            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
        <div style="flex:1">
            <div style="font-size:16px;font-weight:700">{{ auth()->user()->name }}</div>
            <div class="text-sm text-muted">{{ auth()->user()->email }}</div>
            <div style="display:flex;gap:12px;margin-top:8px">
                <span class="badge blue">{{ ucfirst(auth()->user()->role) }}</span>
                @if(auth()->user()->membership_number)
                    <span class="font-mono text-sm" style="color:var(--text-2)">{{ auth()->user()->membership_number }}</span>
                @endif
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;text-align:center">
            <div>
                <div style="font-size:22px;font-weight:700;color:var(--blue)">{{ $totalBookings }}</div>
                <div class="text-sm text-muted">Total Booking</div>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;color:var(--green)">{{ $totalCheckouts }}</div>
                <div class="text-sm text-muted">Total Checkout</div>
            </div>
        </div>
    </div>
</div>
@endsection
