@extends('layouts.app')
@section('title', 'Peralatan')
@section('page-title', 'Manajemen Peralatan')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Peralatan</div>
        <div class="page-subtitle">Kelola inventaris peralatan studio</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalAdd')"><i class="ti ti-plus"></i> Tambah Peralatan</button>
</div>

{{-- FILTER BAR --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:14px 20px">
        <form method="GET" action="{{ route('admin.equipment.index') }}">
            <div class="filter-bar">
                <div class="search-bar">
                    <i class="ti ti-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama / kode..." value="{{ request('search') }}" style="width:220px">
                </div>
                <select name="category" class="form-control" style="width:140px">
                    <option value="">Semua Kategori</option>
                    @foreach(['camera'=>'Kamera','audio'=>'Audio','lighting'=>'Lighting','computer'=>'Komputer','other'=>'Lainnya'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('category')==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control" style="width:130px">
                    <option value="">Semua Status</option>
                    @foreach(['available'=>'Tersedia','checked_out'=>'Dipinjam','maintenance'=>'Maintenance','retired'=>'Pensiun'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-filter"></i> Filter</button>
                @if(request()->hasAny(['search','category','status']))
                    <a href="{{ route('admin.equipment.index') }}" class="btn btn-secondary btn-sm"><i class="ti ti-x"></i> Reset</a>
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
                    <th>Peralatan</th>
                    <th>Kode</th>
                    <th>Kategori</th>
                    <th>Kondisi</th>
                    <th>Status</th>
                    <th>Lokasi</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($equipment as $item)
            <tr>
                <td>
                    <div class="fw-600">{{ $item->name }}</div>
                    @if($item->brand)<div class="text-sm text-muted">{{ $item->brand }} {{ $item->model }}</div>@endif
                </td>
                <td><span class="font-mono text-sm" style="background:var(--bg);padding:3px 8px;border-radius:5px">{{ $item->code }}</span></td>
                <td>
                    @php $cats=['camera'=>['blue','Kamera'],'audio'=>['green','Audio'],'lighting'=>['amber','Lighting'],'computer'=>['purple','Komputer'],'other'=>['gray','Lainnya']]; $cat=$cats[$item->category]??['gray','?']; @endphp
                    <span class="badge {{ $cat[0] }}">{{ $cat[1] }}</span>
                </td>
                <td>
                    @php $conds=['excellent'=>['green','Sempurna'],'good'=>['blue','Baik'],'fair'=>['amber','Cukup'],'needs_repair'=>['red','Perlu Perbaikan']]; $cond=$conds[$item->condition]??['gray','?']; @endphp
                    <span class="badge {{ $cond[0] }}">{{ $cond[1] }}</span>
                </td>
                <td>
                    @php $stats=['available'=>['green','Tersedia'],'checked_out'=>['blue','Dipinjam'],'maintenance'=>['amber','Maintenance'],'retired'=>['gray','Pensiun']]; $st=$stats[$item->status]??['gray','?']; @endphp
                    <span class="badge {{ $st[0] }}"><span class="badge-dot" style="background:currentColor"></span>{{ $st[1] }}</span>
                </td>
                <td class="text-sm text-muted">{{ $item->location }}</td>
                <td style="text-align:right">
                    <div class="d-flex gap-8" style="justify-content:flex-end">
                        <button class="btn btn-secondary btn-sm btn-icon" onclick="editEquipment({{ json_encode($item) }})" title="Edit">
                            <i class="ti ti-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.equipment.destroy', $item->id) }}" onsubmit="return confirm('Hapus peralatan ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><i class="ti ti-camera-off"></i></div><div class="empty-title">Belum ada peralatan</div><div class="empty-desc">Tambah peralatan pertama Anda</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($equipment->hasPages())
    <div class="pagination">
        @if($equipment->onFirstPage()) <span class="page-btn" style="opacity:.4"><i class="ti ti-chevron-left"></i></span>
        @else <a href="{{ $equipment->previousPageUrl() }}" class="page-btn"><i class="ti ti-chevron-left"></i></a> @endif
        @foreach($equipment->getUrlRange(max(1,$equipment->currentPage()-2), min($equipment->lastPage(),$equipment->currentPage()+2)) as $page=>$url)
            <a href="{{ $url }}" class="page-btn {{ $page==$equipment->currentPage()?'active':'' }}">{{ $page }}</a>
        @endforeach
        @if($equipment->hasMorePages()) <a href="{{ $equipment->nextPageUrl() }}" class="page-btn"><i class="ti ti-chevron-right"></i></a>
        @else <span class="page-btn" style="opacity:.4"><i class="ti ti-chevron-right"></i></span> @endif
        <span class="page-info">{{ $equipment->firstItem() }}–{{ $equipment->lastItem() }} dari {{ $equipment->total() }} item</span>
    </div>
    @endif
</div>

{{-- MODAL ADD --}}
<div class="modal-backdrop" id="modalAdd" onclick="if(event.target===this)closeModal('modalAdd')">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Tambah Peralatan</div>
            <button onclick="closeModal('modalAdd')" class="btn btn-secondary btn-sm btn-icon"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.equipment.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group mb-0">
                        <label class="form-label">Nama Peralatan *</label>
                        <input type="text" name="name" class="form-control" placeholder="Canon EOS R5" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Kode *</label>
                        <input type="text" name="code" class="form-control" placeholder="CAM-006" required>
                    </div>
                </div>
                <div class="form-grid" style="margin-top:16px">
                    <div class="form-group mb-0">
                        <label class="form-label">Kategori *</label>
                        <select name="category" class="form-control" required>
                            <option value="">Pilih kategori</option>
                            <option value="camera">Kamera</option>
                            <option value="audio">Audio</option>
                            <option value="lighting">Lighting</option>
                            <option value="computer">Komputer</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Kondisi</label>
                        <select name="condition" class="form-control">
                            <option value="excellent">Sempurna</option>
                            <option value="good" selected>Baik</option>
                            <option value="fair">Cukup</option>
                            <option value="needs_repair">Perlu Perbaikan</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid" style="margin-top:16px">
                    <div class="form-group mb-0">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control" placeholder="Canon">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-control" placeholder="EOS R5">
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px">
                    <label class="form-label">Lokasi Penyimpanan</label>
                    <input type="text" name="location" class="form-control" placeholder="Cabinet A-1" value="Storage Room">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Nomor Seri</label>
                    <input type="text" name="serial_number" class="form-control" placeholder="SN-XXXXX">
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
            <div class="modal-title">Edit Peralatan</div>
            <button onclick="closeModal('modalEdit')" class="btn btn-secondary btn-sm btn-icon"><i class="ti ti-x"></i></button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group mb-0">
                        <label class="form-label">Nama Peralatan *</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Kode *</label>
                        <input type="text" name="code" id="editCode" class="form-control" required>
                    </div>
                </div>
                <div class="form-grid" style="margin-top:16px">
                    <div class="form-group mb-0">
                        <label class="form-label">Kategori</label>
                        <select name="category" id="editCategory" class="form-control">
                            <option value="camera">Kamera</option><option value="audio">Audio</option>
                            <option value="lighting">Lighting</option><option value="computer">Komputer</option><option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Status</label>
                        <select name="status" id="editStatus" class="form-control">
                            <option value="available">Tersedia</option><option value="checked_out">Dipinjam</option>
                            <option value="maintenance">Maintenance</option><option value="retired">Pensiun</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px">
                    <label class="form-label">Kondisi</label>
                    <select name="condition" id="editCondition" class="form-control">
                        <option value="excellent">Sempurna</option><option value="good">Baik</option>
                        <option value="fair">Cukup</option><option value="needs_repair">Perlu Perbaikan</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="location" id="editLocation" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalEdit')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ti ti-check"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editEquipment(item) {
    document.getElementById('editForm').action = `/admin/equipment/${item.id}`;
    document.getElementById('editName').value = item.name;
    document.getElementById('editCode').value = item.code;
    document.getElementById('editCategory').value = item.category;
    document.getElementById('editStatus').value = item.status;
    document.getElementById('editCondition').value = item.condition;
    document.getElementById('editLocation').value = item.location || '';
    openModal('modalEdit');
}
</script>
@endpush
@endsection
