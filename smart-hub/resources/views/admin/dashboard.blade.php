@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Selamat datang, {{ explode(' ', auth()->user()->name)[0] }} </div>
        <div class="page-subtitle">Ringkasan aktivitas Smart-Hub hari ini — {{ now()->isoFormat('dddd, D MMMM Y') }}</div>
    </div>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Booking Baru</a>
</div>

{{-- STATS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ti ti-camera"></i></div>
        <div class="stat-value">{{ $stats['equipment']['available'] }}</div>
        <div class="stat-label">Peralatan Tersedia</div>
        <div class="stat-delta warn"><i class="ti ti-arrow-right" style="font-size:11px"></i> {{ $stats['equipment']['checked_out'] }} sedang dipinjam</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="ti ti-door"></i></div>
        <div class="stat-value">{{ $stats['rooms_available'] }}</div>
        <div class="stat-label">Ruangan Tersedia</div>
        <div class="stat-delta up"><i class="ti ti-check" style="font-size:11px"></i> dari {{ $stats['rooms_total'] }} total ruangan</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="ti ti-clock"></i></div>
        <div class="stat-value">{{ $stats['bookings_30d']['pending'] }}</div>
        <div class="stat-label">Menunggu Konfirmasi</div>
        <div class="stat-delta warn"><i class="ti ti-alert-triangle" style="font-size:11px"></i> perlu tindakan segera</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="ti ti-package-export"></i></div>
        <div class="stat-value">{{ count($stats['overdue_checkouts']) }}</div>
        <div class="stat-label">Checkout Terlambat</div>
        <div class="stat-delta {{ count($stats['overdue_checkouts']) > 0 ? 'warn' : 'up' }}">
            {{ count($stats['overdue_checkouts']) > 0 ? 'Segera hubungi peminjam' : 'Semua tepat waktu' }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="ti ti-users"></i></div>
        <div class="stat-value">{{ $stats['active_members'] }}</div>
        <div class="stat-label">Anggota Aktif</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ti ti-calendar-check"></i></div>
        <div class="stat-value">{{ $stats['bookings_30d']['total'] }}</div>
        <div class="stat-label">Booking 30 Hari</div>
        <div class="stat-delta up"><i class="ti ti-check" style="font-size:11px"></i> {{ $stats['bookings_30d']['confirmed'] }} terkonfirmasi</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    {{-- TODAY'S BOOKINGS --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="ti ti-calendar" style="font-size:15px;vertical-align:-2px;margin-right:6px;color:var(--blue)"></i>Booking Hari Ini</div>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        @if(count($stats['today_bookings']) > 0)
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ruangan</th><th>Anggota</th><th>Waktu</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($stats['today_bookings'] as $b)
                <tr>
                    <td class="fw-600">{{ $b->room->name ?? '-' }}</td>
                    <td>{{ $b->user->name ?? '-' }}</td>
                    <td class="font-mono text-sm">
                        {{ \Carbon\Carbon::parse($b->start_datetime)->format('H:i') }} –
                        {{ \Carbon\Carbon::parse($b->end_datetime)->format('H:i') }}
                    </td>
                    <td>
                        @php $sc = ['pending'=>'amber','confirmed'=>'blue','ongoing'=>'green','completed'=>'gray','cancelled'=>'red'][$b->status] ?? 'gray'; @endphp
                        <span class="badge {{ $sc }}">{{ ucfirst($b->status) }}</span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:30px">
            <div class="empty-icon"><i class="ti ti-calendar-off"></i></div>
            <div class="empty-title">Tidak ada booking hari ini</div>
        </div>
        @endif
    </div>

    {{-- OVERDUE CHECKOUTS --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="ti ti-alert-triangle" style="font-size:15px;vertical-align:-2px;margin-right:6px;color:var(--amber)"></i>Checkout Terlambat</div>
            <a href="{{ route('admin.checkouts.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        @if(count($stats['overdue_checkouts']) > 0)
        <div class="table-wrap">
            <table>
                <thead><tr><th>Peralatan</th><th>Peminjam</th><th>Batas Kembali</th></tr></thead>
                <tbody>
                @foreach($stats['overdue_checkouts'] as $c)
                <tr>
                    <td class="fw-600">{{ $c->equipment->name ?? '-' }}</td>
                    <td>{{ $c->user->name ?? '-' }}</td>
                    <td>
                        <span class="badge red">
                            <i class="ti ti-clock" style="font-size:11px"></i>
                            {{ \Carbon\Carbon::parse($c->expected_return_at)->diffForHumans() }}
                        </span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:30px">
            <div class="empty-icon" style="color:var(--green)"><i class="ti ti-circle-check"></i></div>
            <div class="empty-title">Tidak ada keterlambatan</div>
            <div class="empty-desc">Semua peralatan dikembalikan tepat waktu</div>
        </div>
        @endif
    </div>

</div>

{{-- EQUIPMENT STATUS --}}
<div class="card" style="margin-top:20px">
    <div class="card-header">
        <div class="card-title"><i class="ti ti-camera" style="font-size:15px;vertical-align:-2px;margin-right:6px;color:var(--blue)"></i>Status Peralatan</div>
        <a href="{{ route('admin.equipment.index') }}" class="btn btn-secondary btn-sm">Kelola Peralatan</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-top:1px solid var(--border)">
        @foreach([['Tersedia','available','green','camera'],['Dipinjam','checked_out','blue','package-export'],['Maintenance','maintenance','amber','tool'],['Pensiun','retired','gray','archive']] as [$label,$key,$color,$icon])
        <div style="padding:20px;text-align:center;{{ !$loop->last ? 'border-right:1px solid var(--border)' : '' }}">
            <div style="font-size:24px;font-weight:700;color:var(--{{ $color }})">{{ $stats['equipment'][$key] ?? 0 }}</div>
            <div style="font-size:12px;color:var(--text-3);margin-top:4px;font-weight:500">{{ $label }}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection
