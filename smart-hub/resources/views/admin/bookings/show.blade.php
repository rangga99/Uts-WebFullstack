@extends('layouts.app')
@section('title', 'Detail Booking')
@section('page-title', 'Detail Booking')
@section('breadcrumb')
    <a href="{{ route('admin.bookings.index') }}">Booking</a>
    <i class="ti ti-chevron-right" style="font-size:12px"></i>
    <span>{{ $booking->booking_code }}</span>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

    {{-- LEFT --}}
    <div style="display:flex;flex-direction:column;gap:20px">

        {{-- INFO BOOKING --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Informasi Booking</div>
                @php $sc=['pending'=>'amber','confirmed'=>'blue','ongoing'=>'green','completed'=>'gray','cancelled'=>'red'][$booking->status]??'gray'; @endphp
                <span class="badge {{ $sc }}" style="font-size:13px;padding:5px 12px"><span class="badge-dot" style="background:currentColor"></span>{{ ucfirst($booking->status) }}</span>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div>
                        <div class="text-sm text-muted">Kode Booking</div>
                        <div class="fw-600 font-mono" style="margin-top:3px">{{ $booking->booking_code }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-muted">Tanggal Dibuat</div>
                        <div class="fw-600" style="margin-top:3px">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y, H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-muted">Mulai</div>
                        <div class="fw-600" style="margin-top:3px">{{ \Carbon\Carbon::parse($booking->start_datetime)->isoFormat('dddd, D MMMM Y') }}</div>
                        <div class="text-sm text-muted">{{ \Carbon\Carbon::parse($booking->start_datetime)->format('H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-muted">Selesai</div>
                        <div class="fw-600" style="margin-top:3px">{{ \Carbon\Carbon::parse($booking->end_datetime)->isoFormat('dddd, D MMMM Y') }}</div>
                        <div class="text-sm text-muted">{{ \Carbon\Carbon::parse($booking->end_datetime)->format('H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-muted">Durasi</div>
                        <div class="fw-600" style="margin-top:3px">{{ $booking->duration_hours }} jam</div>
                    </div>
                    <div>
                        <div class="text-sm text-muted">Total Biaya</div>
                        <div class="fw-600" style="margin-top:3px;color:var(--blue);font-size:16px">Rp {{ number_format($booking->total_price,0,',','.') }}</div>
                    </div>
                </div>
                @if($booking->notes)
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
                    <div class="text-sm text-muted">Catatan</div>
                    <div style="margin-top:6px;color:var(--text-2)">{{ $booking->notes }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- ADMIN ACTIONS --}}
        @if($booking->status === 'pending')
        <div class="card">
            <div class="card-header"><div class="card-title">Tindakan Admin</div></div>
            <div class="card-body" style="display:flex;gap:12px">
                <form method="POST" action="{{ route('admin.bookings.confirm', $booking->id) }}" style="flex:1">
                    @csrf @method('PUT')
                    <button type="submit" class="btn btn-primary" style="width:100%"><i class="ti ti-check"></i> Konfirmasi Booking</button>
                </form>
                <form method="POST" action="{{ route('admin.bookings.cancel', $booking->id) }}" style="flex:1" onsubmit="return confirm('Batalkan booking ini?')">
                    @csrf @method('POST')
                    <button type="submit" class="btn btn-danger" style="width:100%"><i class="ti ti-x"></i> Tolak Booking</button>
                </form>
            </div>
        </div>
        @endif

        {{-- EQUIPMENT CHECKOUTS terkait --}}
        @php $coList = collect((array)($booking->checkouts ?? [])); @endphp
        @if($coList->count() > 0)
        <div class="card">
            <div class="card-header"><div class="card-title">Peralatan Terkait</div></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Peralatan</th><th>Checkout</th><th>Batas Kembali</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach($coList as $co)
                    <tr>
                        <td class="fw-600">{{ $co->equipment->name ?? '-' }}</td>
                        <td class="text-sm">{{ \Carbon\Carbon::parse($co->checked_out_at)->format('d M H:i') }}</td>
                        <td class="text-sm">{{ \Carbon\Carbon::parse($co->expected_return_at)->format('d M H:i') }}</td>
                        <td><span class="badge {{ ['active'=>'blue','returned'=>'green','overdue'=>'red','lost'=>'gray'][$co->status]??'gray' }}">{{ ucfirst($co->status) }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- MEMBER --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Anggota</div></div>
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                    <div class="user-avatar" style="width:42px;height:42px;font-size:15px">{{ strtoupper(substr($booking->user->name ?? '?', 0, 2)) }}</div>
                    <div>
                        <div class="fw-600">{{ $booking->user->name ?? '-' }}</div>
                        <div class="text-sm text-muted">{{ data_get($booking, 'user.membership_number', '-') }}</div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <div style="display:flex;justify-content:space-between">
                        <span class="text-sm text-muted">Email</span>
                        <span class="text-sm fw-600">{{ $booking->user->email }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span class="text-sm text-muted">Telepon</span>
                        <span class="text-sm fw-600">{{ $booking->user->phone ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROOM --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Ruangan</div></div>
            <div class="card-body">
                <div class="fw-600" style="font-size:15px;margin-bottom:4px">{{ $booking->room->name }}</div>
                <span class="badge blue" style="margin-bottom:12px">{{ ucfirst($booking->room->type) }}</span>
                <div style="display:flex;flex-direction:column;gap:8px;margin-top:10px">
                    <div style="display:flex;justify-content:space-between">
                        <span class="text-sm text-muted">Kode</span>
                        <span class="font-mono text-sm">{{ $booking->room->code }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span class="text-sm text-muted">Kapasitas</span>
                        <span class="text-sm fw-600">{{ $booking->room->capacity }} orang</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span class="text-sm text-muted">Tarif/jam</span>
                        <span class="text-sm fw-600">Rp {{ number_format($booking->room->price_per_hour,0,',','.') }}</span>
                    </div>
                </div>
                @if($booking->room->facilities)
                <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
                    <div class="text-sm text-muted" style="margin-bottom:8px">Fasilitas</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px">
                        @foreach($booking->room->facilities as $f)
                            <span class="badge gray">{{ $f }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary" style="width:100%;justify-content:center"><i class="ti ti-arrow-left"></i> Kembali ke Daftar</a>
    </div>
</div>
@endsection
