@extends('layouts.app')
@section('title', 'Ruangan')
@section('page-title', 'Manajemen Ruangan')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Ruangan</div>
        <div class="page-subtitle">Kelola ruang kerja dan studio</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalAdd')"><i class="ti ti-plus"></i> Tambah Ruangan</button>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px">
@forelse($rooms as $room)
<div class="card" style="transition:box-shadow .18s,border-color .18s" onmouseenter="this.style.boxShadow='0 6px 20px rgba(0,0,0,.09)';this.style.borderColor='var(--border-2)'" onmouseleave="this.style.boxShadow='';this.style.borderColor=''">
    <div style="padding:20px 22px;border-bottom:1px solid var(--border)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--text-1)">{{ $room->name }}</div>
                <span class="font-mono text-sm" style="background:var(--bg);padding:2px 8px;border-radius:5px;margin-top:4px;display:inline-block">{{ $room->code }}</span>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0">
                @php $types=['workspace'=>'blue','studio'=>'purple','meeting'=>'green']; @endphp
                <span class="badge {{ $types[$room->type]??'gray' }}">{{ ucfirst($room->type) }}</span>
                <span class="badge {{ $room->is_available ? 'green' : 'red' }}">{{ $room->is_available ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
        </div>
    </div>
    <div style="padding:16px 22px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div>
                <div class="text-sm text-muted">Kapasitas</div>
                <div class="fw-600" style="margin-top:2px"><i class="ti ti-users" style="font-size:13px;margin-right:4px"></i>{{ $room->capacity }} orang</div>
            </div>
            <div>
                <div class="text-sm text-muted">Tarif / jam</div>
                <div class="fw-600" style="margin-top:2px;color:var(--blue)">Rp {{ number_format($room->price_per_hour,0,',','.') }}</div>
            </div>
        </div>
        @if($room->facilities && count($room->facilities) > 0)
        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:14px">
            @foreach(array_slice($room->facilities, 0, 4) as $f)
                <span class="badge gray" style="font-size:11px">{{ $f }}</span>
            @endforeach
            @if(count($room->facilities) > 4)
                <span class="badge gray" style="font-size:11px">+{{ count($room->facilities)-4 }} lainnya</span>
            @endif
        </div>
        @endif
        <div style="display:flex;gap:8px">
            <button class="btn btn-secondary btn-sm" style="flex:1;justify-content:center" onclick="editRoom({{ json_encode($room) }})"><i class="ti ti-edit"></i> Edit</button>
            <form method="POST" action="{{ route('admin.rooms.destroy', $room->id) }}" onsubmit="return confirm('Hapus ruangan ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="ti ti-trash"></i></button>
            </form>
            <form method="POST" action="{{ route('admin.rooms.toggle', $room->id) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm btn-icon" style="background:var(--bg);border:1px solid var(--border-2)" title="{{ $room->is_available ? 'Nonaktifkan' : 'Aktifkan' }}">
                    <i class="ti ti-{{ $room->is_available ? 'toggle-right' : 'toggle-left' }}" style="color:var(--{{ $room->is_available ? 'green' : 'text-3' }})"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@empty
<div class="card" style="grid-column:1/-1">
    <div class="empty-state">
        <div class="empty-icon"><i class="ti ti-door-off"></i></div>
        <div class="empty-title">Belum ada ruangan</div>
        <div class="empty-desc">Tambahkan ruangan pertama</div>
    </div>
</div>
@endforelse
</div>

{{-- MODAL ADD --}}
<div class="modal-backdrop" id="modalAdd" onclick="if(event.target===this)closeModal('modalAdd')">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Tambah Ruangan</div>
            <button onclick="closeModal('modalAdd')" class="btn btn-secondary btn-sm btn-icon"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.rooms.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group mb-0">
                        <label class="form-label">Nama Ruangan *</label>
                        <input type="text" name="name" class="form-control" placeholder="Studio A" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Kode *</label>
                        <input type="text" name="code" class="form-control" placeholder="STUDIO-A" required>
                    </div>
                </div>
                <div class="form-grid" style="margin-top:16px">
                    <div class="form-group mb-0">
                        <label class="form-label">Tipe *</label>
                        <select name="type" class="form-control" required>
                            <option value="workspace">Workspace</option>
                            <option value="studio">Studio</option>
                            <option value="meeting">Meeting Room</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Kapasitas (orang) *</label>
                        <input type="number" name="capacity" class="form-control" placeholder="10" min="1" required>
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px">
                    <label class="form-label">Tarif per Jam (Rp) *</label>
                    <input type="number" name="price_per_hour" class="form-control" placeholder="75000" min="0" required>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Fasilitas <span class="text-muted">(pisahkan dengan koma)</span></label>
                    <input type="text" name="facilities_input" class="form-control" placeholder="AC, Proyektor, Whiteboard">
                    <div class="form-hint">Contoh: AC, Proyektor, WiFi, Whiteboard</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalAdd')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ti ti-plus"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal-backdrop" id="modalEdit" onclick="if(event.target===this)closeModal('modalEdit')">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Edit Ruangan</div>
            <button onclick="closeModal('modalEdit')" class="btn btn-secondary btn-sm btn-icon"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="editRoomForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group mb-0">
                        <label class="form-label">Nama Ruangan</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Kode</label>
                        <input type="text" name="code" id="editCode" class="form-control" required>
                    </div>
                </div>
                <div class="form-grid" style="margin-top:16px">
                    <div class="form-group mb-0">
                        <label class="form-label">Tipe</label>
                        <select name="type" id="editType" class="form-control">
                            <option value="workspace">Workspace</option>
                            <option value="studio">Studio</option>
                            <option value="meeting">Meeting Room</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Kapasitas</label>
                        <input type="number" name="capacity" id="editCapacity" class="form-control" min="1">
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px">
                    <label class="form-label">Tarif per Jam (Rp)</label>
                    <input type="number" name="price_per_hour" id="editPrice" class="form-control">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Fasilitas</label>
                    <input type="text" name="facilities_input" id="editFacilities" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ti ti-check"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editRoom(room) {
    document.getElementById('editRoomForm').action = `/admin/rooms/${room.id}`;
    document.getElementById('editName').value = room.name;
    document.getElementById('editCode').value = room.code;
    document.getElementById('editType').value = room.type;
    document.getElementById('editCapacity').value = room.capacity;
    document.getElementById('editPrice').value = room.price_per_hour;
    document.getElementById('editFacilities').value = Array.isArray(room.facilities) ? room.facilities.join(', ') : '';
    openModal('modalEdit');
}
</script>
@endpush
@endsection
