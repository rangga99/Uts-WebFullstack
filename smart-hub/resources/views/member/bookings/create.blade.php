@extends('layouts.app')
@section('title', 'Booking Ruangan')
@section('page-title', 'Booking Ruangan')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Pesan Ruangan</div>
        <div class="page-subtitle">Pilih ruangan dan tentukan jadwal</div>
    </div>
    <a href="{{ route('member.bookings.index') }}" class="btn btn-secondary"><i class="ti ti-list"></i> Booking Saya</a>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">

    {{-- FORM --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="ti ti-calendar-plus" style="font-size:15px;vertical-align:-2px;margin-right:6px;color:var(--blue)"></i>Form Booking Baru</div>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-error">
                    <i class="ti ti-alert-circle"></i>
                    <div>
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('member.bookings.store') }}" id="bookingForm">
                @csrf

                {{-- ROOM SELECTION --}}
                <div class="form-group">
                    <label class="form-label">Pilih Ruangan *</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        @foreach($rooms as $room)
                        <label style="cursor:pointer">
                            <input type="radio" name="room_id" value="{{ $room->id }}" style="display:none" {{ old('room_id')==$room->id?'checked':'' }} onchange="selectRoom(this, {!! json_encode($room) !!})">
                            <div class="room-card" id="room-{{ $room->id }}" style="border:2px solid var(--border);border-radius:var(--radius);padding:14px;transition:all .18s">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                                    <div style="font-size:13.5px;font-weight:700">{{ $room->name }}</div>
                                    <span class="badge {{ ['workspace'=>'blue','studio'=>'purple','meeting'=>'green'][$room->type]??'gray' }}" style="font-size:10.5px">{{ ucfirst($room->type) }}</span>
                                </div>
                                <div class="text-sm text-muted" style="margin-bottom:6px"><i class="ti ti-users" style="font-size:12px;margin-right:3px"></i>{{ $room->capacity }} orang</div>
                                <div style="font-size:13px;font-weight:700;color:var(--blue)">Rp {{ number_format($room->price_per_hour,0,',','.') }}/jam</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('room_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- DATE TIME --}}
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Waktu Mulai *</label>
                        <input type="datetime-local" name="start_datetime" id="startTime" class="form-control"
                               value="{{ old('start_datetime') }}" min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                               required onchange="updateSummary()">
                        @error('start_datetime')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Waktu Selesai *</label>
                        <input type="datetime-local" name="end_datetime" id="endTime" class="form-control"
                               value="{{ old('end_datetime') }}" required onchange="updateSummary()">
                        @error('end_datetime')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan / Keperluan</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Untuk keperluan apa ruangan ini dipesan...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
                    <i class="ti ti-calendar-check"></i> Kirim Permintaan Booking
                </button>
            </form>
        </div>
    </div>

    {{-- SUMMARY SIDEBAR --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        {{-- BOOKING SUMMARY --}}
        <div class="card" id="summaryCard">
            <div class="card-header"><div class="card-title">Ringkasan Booking</div></div>
            <div class="card-body">
                <div id="summaryEmpty" style="text-align:center;padding:20px 0;color:var(--text-3)">
                    <i class="ti ti-mouse" style="font-size:32px;display:block;margin-bottom:8px"></i>
                    <div class="text-sm">Pilih ruangan dan jadwal untuk melihat ringkasan</div>
                </div>
                <div id="summaryContent" style="display:none">
                    <div style="display:flex;flex-direction:column;gap:12px">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span class="text-sm text-muted">Ruangan</span>
                            <span class="fw-600 text-sm" id="sumRoom">—</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span class="text-sm text-muted">Mulai</span>
                            <span class="text-sm" id="sumStart">—</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span class="text-sm text-muted">Selesai</span>
                            <span class="text-sm" id="sumEnd">—</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span class="text-sm text-muted">Durasi</span>
                            <span class="fw-600 text-sm" id="sumDuration">—</span>
                        </div>
                        <div style="border-top:1px solid var(--border);padding-top:12px;display:flex;justify-content:space-between;align-items:center">
                            <span class="fw-600">Total Estimasi</span>
                            <span style="font-size:17px;font-weight:700;color:var(--blue)" id="sumTotal">—</span>
                        </div>
                    </div>
                    <div class="alert alert-warning" style="margin-top:14px;margin-bottom:0;font-size:12.5px">
                        <i class="ti ti-info-circle"></i> Booking perlu dikonfirmasi admin sebelum berlaku
                    </div>
                </div>
            </div>
        </div>

        {{-- ROOMS QUICK INFO --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Info Ruangan</div></div>
            <div id="roomInfo" style="padding:16px 20px">
                <div class="text-sm text-muted text-center" style="padding:10px 0">Pilih ruangan untuk melihat detail</div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
input[type=radio]:checked + .room-card {
    border-color: var(--blue) !important;
    background: var(--blue-light);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.room-card:hover { border-color: var(--border-2) !important; }
</style>
@endpush

@push('scripts')
<script>
let selectedRoom = null;
const rooms = @json($rooms->mapWithKeys(fn ($r) => [$r->id => $r]));

function selectRoom(input, room) {
    selectedRoom = room;
    document.querySelectorAll('.room-card').forEach(c => { c.style.borderColor = 'var(--border)'; c.style.background = ''; });
    document.getElementById('room-'+room.id).style.borderColor = 'var(--blue)';
    document.getElementById('room-'+room.id).style.background = 'var(--blue-light)';

    // Show room info
    const fac = Array.isArray(room.facilities) ? room.facilities.join(', ') : '—';
    document.getElementById('roomInfo').innerHTML = `
        <div style="display:flex;flex-direction:column;gap:10px">
            <div style="display:flex;justify-content:space-between"><span class="text-sm text-muted">Kode</span><span class="font-mono text-sm">${room.code}</span></div>
            <div style="display:flex;justify-content:space-between"><span class="text-sm text-muted">Kapasitas</span><span class="text-sm fw-600">${room.capacity} orang</span></div>
            <div style="display:flex;justify-content:space-between"><span class="text-sm text-muted">Tarif/jam</span><span class="text-sm fw-600" style="color:var(--blue)">Rp ${Number(room.price_per_hour).toLocaleString('id')}</span></div>
            <div style="border-top:1px solid var(--border);padding-top:10px"><div class="text-sm text-muted" style="margin-bottom:6px">Fasilitas</div><div class="text-sm">${fac}</div></div>
        </div>`;
    updateSummary();
}

function updateSummary() {
    const s = document.getElementById('startTime').value;
    const e = document.getElementById('endTime').value;
    if (!selectedRoom || !s || !e) { document.getElementById('summaryEmpty').style.display='block'; document.getElementById('summaryContent').style.display='none'; return; }
    const start = new Date(s), end = new Date(e);
    if (end <= start) return;
    const hours = (end - start) / 3600000;
    const total = hours * selectedRoom.price_per_hour;
    const fmt = d => d.toLocaleString('id-ID',{weekday:'short',day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'});
    document.getElementById('summaryEmpty').style.display = 'none';
    document.getElementById('summaryContent').style.display = 'block';
    document.getElementById('sumRoom').textContent = selectedRoom.name;
    document.getElementById('sumStart').textContent = fmt(start);
    document.getElementById('sumEnd').textContent = fmt(end);
    document.getElementById('sumDuration').textContent = hours.toFixed(1) + ' jam';
    document.getElementById('sumTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
</script>
@endpush
@endsection
