<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart-Hub') — Smart-Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --white:       #ffffff;
            --bg:          #f5f6f8;
            --surface:     #ffffff;
            --border:      #e4e6ec;
            --border-2:    #c8ccd8;
            --text-1:      #0d1117;
            --text-2:      #4b5263;
            --text-3:      #8892a4;
            --blue:        #2563eb;
            --blue-hover:  #1d4ed8;
            --blue-light:  #eff6ff;
            --blue-mid:    #dbeafe;
            --green:       #059669;
            --green-light: #ecfdf5;
            --amber:       #d97706;
            --amber-light: #fffbeb;
            --red:         #dc2626;
            --red-light:   #fef2f2;
            --purple:      #7c3aed;
            --purple-light:#f5f3ff;
            --sidebar:     220px;
            --topbar:      60px;
            --radius:      10px;
            --radius-sm:   6px;
            --radius-lg:   14px;
            --shadow-sm:   0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
            --shadow:      0 4px 12px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.05);
            --font:        'Plus Jakarta Sans', sans-serif;
            --mono:        'JetBrains Mono', monospace;
            --transition:  0.18s ease;
        }

        body { font-family: var(--font); background: var(--bg); color: var(--text-1); font-size: 14px; line-height: 1.6; -webkit-font-smoothing: antialiased; }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed; top: 0; left: 0; width: var(--sidebar); height: 100vh;
            background: var(--white); border-right: 1px solid var(--border);
            display: flex; flex-direction: column; z-index: 200; transition: transform var(--transition);
        }
        .sidebar-logo {
            height: var(--topbar); display: flex; align-items: center; gap: 10px;
            padding: 0 18px; border-bottom: 1px solid var(--border); flex-shrink: 0;
        }
        .logo-icon {
            width: 30px; height: 30px; background: var(--blue); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .logo-icon i { color: #fff; font-size: 15px; }
        .logo-text { font-size: 14px; font-weight: 700; color: var(--text-1); letter-spacing: -0.3px; }
        .logo-badge {
            font-size: 9px; font-weight: 600; background: var(--blue-light); color: var(--blue);
            padding: 1px 6px; border-radius: 4px; letter-spacing: .3px; text-transform: uppercase;
        }

        .sidebar-section { padding: 8px 10px 4px; }
        .sidebar-section-label { font-size: 10px; font-weight: 600; color: var(--text-3); letter-spacing: .8px; text-transform: uppercase; padding: 0 8px 4px; }

        .nav-item {
            display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: var(--radius-sm);
            color: var(--text-2); text-decoration: none; font-size: 13.5px; font-weight: 500;
            transition: background var(--transition), color var(--transition); cursor: pointer; border: none; background: none; width: 100%;
        }
        .nav-item:hover { background: var(--bg); color: var(--text-1); }
        .nav-item.active { background: var(--blue-light); color: var(--blue); }
        .nav-item i { font-size: 17px; flex-shrink: 0; }
        .nav-badge {
            margin-left: auto; font-size: 10px; font-weight: 600; background: var(--red);
            color: #fff; padding: 1px 6px; border-radius: 10px; min-width: 18px; text-align: center;
        }

        .sidebar-footer {
            margin-top: auto; border-top: 1px solid var(--border); padding: 12px 10px;
        }
        .user-card {
            display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--radius-sm);
            transition: background var(--transition); text-decoration: none; cursor: pointer;
        }
        .user-card:hover { background: var(--bg); }
        .user-avatar {
            width: 32px; height: 32px; border-radius: 50%; background: var(--blue-mid);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            font-size: 12px; font-weight: 700; color: var(--blue);
        }
        .user-name { font-size: 13px; font-weight: 600; color: var(--text-1); }
        .user-role { font-size: 11px; color: var(--text-3); }

        /* ── TOPBAR ── */
        .topbar {
            position: fixed; top: 0; left: var(--sidebar); right: 0; height: var(--topbar);
            background: var(--white); border-bottom: 1px solid var(--border); z-index: 100;
            display: flex; align-items: center; padding: 0 24px; gap: 16px;
        }
        .topbar-title { font-size: 15px; font-weight: 600; color: var(--text-1); }
        .topbar-breadcrumb { display: flex; align-items: center; gap: 6px; color: var(--text-3); font-size: 13px; }
        .topbar-breadcrumb a { color: var(--text-3); text-decoration: none; }
        .topbar-breadcrumb a:hover { color: var(--blue); }
        .topbar-right { margin-left: auto; display: flex; align-items: center; gap: 10px; }

        /* ── MAIN CONTENT ── */
        .main { margin-left: var(--sidebar); padding-top: var(--topbar); min-height: 100vh; }
        .content { padding: 28px 28px; }

        /* ── PAGE HEADER ── */
        .page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; gap: 16px; flex-wrap: wrap; }
        .page-title { font-size: 22px; font-weight: 700; color: var(--text-1); letter-spacing: -0.5px; }
        .page-subtitle { font-size: 13.5px; color: var(--text-2); margin-top: 2px; }

        /* ── STAT CARDS ── */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card {
            background: var(--white); border: 1px solid var(--border); border-radius: var(--radius-lg);
            padding: 20px 22px; transition: box-shadow var(--transition), border-color var(--transition);
        }
        .stat-card:hover { box-shadow: var(--shadow); border-color: var(--border-2); }
        .stat-icon {
            width: 38px; height: 38px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center; margin-bottom: 14px;
        }
        .stat-icon i { font-size: 19px; }
        .stat-icon.blue { background: var(--blue-light); color: var(--blue); }
        .stat-icon.green { background: var(--green-light); color: var(--green); }
        .stat-icon.amber { background: var(--amber-light); color: var(--amber); }
        .stat-icon.red { background: var(--red-light); color: var(--red); }
        .stat-icon.purple { background: var(--purple-light); color: var(--purple); }
        .stat-value { font-size: 26px; font-weight: 700; letter-spacing: -1px; color: var(--text-1); }
        .stat-label { font-size: 12.5px; color: var(--text-3); margin-top: 2px; font-weight: 500; }
        .stat-delta { font-size: 12px; margin-top: 8px; font-weight: 500; }
        .stat-delta.up { color: var(--green); }
        .stat-delta.warn { color: var(--amber); }

        /* ── CARDS ── */
        .card {
            background: var(--white); border: 1px solid var(--border); border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm); overflow: hidden;
        }
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; border-bottom: 1px solid var(--border); gap: 12px; flex-wrap: wrap;
        }
        .card-title { font-size: 14.5px; font-weight: 600; color: var(--text-1); }
        .card-body { padding: 20px; }

        /* ── TABLE ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 10px 16px; text-align: left; font-size: 11.5px; font-weight: 600;
            color: var(--text-3); text-transform: uppercase; letter-spacing: .6px;
            background: var(--bg); border-bottom: 1px solid var(--border);
        }
        tbody td { padding: 13px 16px; border-bottom: 1px solid var(--border); font-size: 13.5px; color: var(--text-2); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr { transition: background var(--transition); }
        tbody tr:hover { background: var(--bg); }

        /* ── BADGES ── */
        .badge {
            display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px;
            border-radius: 20px; font-size: 11.5px; font-weight: 600; white-space: nowrap;
        }
        .badge-dot { width: 6px; height: 6px; border-radius: 50%; }
        .badge.green  { background: var(--green-light); color: #065f46; }
        .badge.blue   { background: var(--blue-light); color: #1e40af; }
        .badge.amber  { background: var(--amber-light); color: #92400e; }
        .badge.red    { background: var(--red-light); color: #991b1b; }
        .badge.purple { background: var(--purple-light); color: #5b21b6; }
        .badge.gray   { background: var(--bg); color: var(--text-2); }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
            border-radius: var(--radius-sm); font-size: 13.5px; font-weight: 600;
            cursor: pointer; border: none; transition: all var(--transition); text-decoration: none; white-space: nowrap;
            font-family: var(--font);
        }
        .btn i { font-size: 16px; }
        .btn-primary { background: var(--blue); color: #fff; }
        .btn-primary:hover { background: var(--blue-hover); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,.3); }
        .btn-secondary { background: var(--white); color: var(--text-1); border: 1px solid var(--border-2); }
        .btn-secondary:hover { background: var(--bg); border-color: var(--border-2); }
        .btn-danger { background: var(--red-light); color: var(--red); border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fecaca; }
        .btn-sm { padding: 5px 11px; font-size: 12.5px; }
        .btn-icon { padding: 7px; border-radius: var(--radius-sm); }

        /* ── FORMS ── */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-1); margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 9px 13px; border: 1px solid var(--border-2); border-radius: var(--radius-sm);
            font-size: 13.5px; font-family: var(--font); color: var(--text-1); background: var(--white);
            transition: border-color var(--transition), box-shadow var(--transition); outline: none;
        }
        .form-control:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .form-control::placeholder { color: var(--text-3); }
        .form-hint { font-size: 12px; color: var(--text-3); margin-top: 5px; }
        .form-error { font-size: 12px; color: var(--red); margin-top: 5px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        select.form-control { cursor: pointer; }

        /* ── INPUT GROUP ── */
        .input-group { position: relative; }
        .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-3); font-size: 16px; pointer-events: none; }
        .input-group .form-control { padding-left: 38px; }

        /* ── ALERTS ── */
        .alert { padding: 12px 16px; border-radius: var(--radius-sm); font-size: 13.5px; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: var(--green-light); color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: var(--red-light); color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background: var(--amber-light); color: #92400e; border: 1px solid #fde68a; }
        .alert-info { background: var(--blue-light); color: #1e40af; border: 1px solid #bfdbfe; }

        /* ── MODALS ── */
        .modal-backdrop {
            position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 500;
            display: flex; align-items: center; justify-content: center; padding: 20px;
            opacity: 0; pointer-events: none; transition: opacity var(--transition);
        }
        .modal-backdrop.open { opacity: 1; pointer-events: all; }
        .modal {
            background: var(--white); border-radius: var(--radius-lg); width: 100%; max-width: 520px;
            box-shadow: 0 20px 60px rgba(0,0,0,.15); transform: translateY(12px);
            transition: transform var(--transition);
        }
        .modal-backdrop.open .modal { transform: translateY(0); }
        .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--border); }
        .modal-title { font-size: 15px; font-weight: 700; }
        .modal-body { padding: 22px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 22px; border-top: 1px solid var(--border); }

        /* ── SEARCH BAR ── */
        .search-bar { position: relative; }
        .search-bar i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text-3); font-size: 16px; }
        .search-bar input { padding-left: 36px; width: 240px; }

        /* ── EMPTY STATE ── */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-icon { font-size: 48px; color: var(--text-3); margin-bottom: 12px; }
        .empty-title { font-size: 15px; font-weight: 600; color: var(--text-1); margin-bottom: 6px; }
        .empty-desc { font-size: 13.5px; color: var(--text-3); }

        /* ── PAGINATION ── */
        .pagination { display: flex; align-items: center; gap: 4px; padding: 16px 20px; border-top: 1px solid var(--border); }
        .page-btn {
            min-width: 32px; height: 32px; border-radius: var(--radius-sm); display: flex;
            align-items: center; justify-content: center; font-size: 13px; font-weight: 500;
            border: 1px solid var(--border); background: var(--white); color: var(--text-2);
            cursor: pointer; text-decoration: none; transition: all var(--transition);
        }
        .page-btn:hover { background: var(--bg); border-color: var(--border-2); }
        .page-btn.active { background: var(--blue); color: #fff; border-color: var(--blue); }
        .page-info { margin-left: auto; font-size: 12.5px; color: var(--text-3); }

        /* ── FILTER BAR ── */
        .filter-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .filter-chip {
            padding: 6px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 500;
            border: 1px solid var(--border-2); background: var(--white); color: var(--text-2);
            cursor: pointer; transition: all var(--transition);
        }
        .filter-chip:hover { border-color: var(--blue); color: var(--blue); background: var(--blue-light); }
        .filter-chip.active { background: var(--blue); color: #fff; border-color: var(--blue); }

        /* ── UTILITIES ── */
        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .gap-8 { gap: 8px; }
        .gap-12 { gap: 12px; }
        .ml-auto { margin-left: auto; }
        .text-sm { font-size: 12.5px; }
        .text-muted { color: var(--text-3); }
        .font-mono { font-family: var(--mono); }
        .fw-600 { font-weight: 600; }
        .mb-0 { margin-bottom: 0; }
        .mt-4 { margin-top: 4px; }

        /* ── TOAST ── */
        .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
        .toast {
            background: var(--text-1); color: #fff; padding: 12px 18px; border-radius: var(--radius);
            font-size: 13.5px; font-weight: 500; box-shadow: var(--shadow); display: flex; align-items: center; gap: 10px;
            animation: slideUp .25s ease; min-width: 280px;
        }
        .toast.success { background: #065f46; }
        .toast.error { background: #991b1b; }
        @keyframes slideUp { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* ── RESPONSIVE ── */
        .hamburger { display: none; }
        @media (max-width: 768px) {
            :root { --sidebar: 0px; }
            .sidebar { transform: translateX(-220px); }
            .sidebar.open { transform: translateX(0); width: 220px; }
            .topbar { left: 0; }
            .main { margin-left: 0; }
            .hamburger { display: flex; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="ti ti-building-community"></i></div>
        <div>
            <div class="logo-text">Smart-Hub</div>
        </div>
        @if(auth()->user()->isAdmin())
            <span class="logo-badge" style="margin-left:auto">Admin</span>
        @endif
    </div>

    <div style="overflow-y:auto; flex:1; padding-bottom:8px;">
        @if(auth()->user()->isAdmin())
        {{-- ADMIN NAV --}}
        <div class="sidebar-section">
            <div class="sidebar-section-label">Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="ti ti-layout-dashboard"></i> Dashboard
            </a>
        </div>
        <div class="sidebar-section">
            <div class="sidebar-section-label">Manajemen</div>
            <a href="{{ route('admin.equipment.index') }}" class="nav-item {{ request()->routeIs('admin.equipment*') ? 'active' : '' }}">
                <i class="ti ti-camera"></i> Peralatan
            </a>
            <a href="{{ route('admin.rooms.index') }}" class="nav-item {{ request()->routeIs('admin.rooms*') ? 'active' : '' }}">
                <i class="ti ti-door"></i> Ruangan
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="nav-item {{ request()->routeIs('admin.bookings*') ? 'active' : '' }}">
                <i class="ti ti-calendar"></i> Booking
                @php
                    // Avoid direct DB query — use cached count from session (set by dashboard controller)
                    // or fall back to 0. The dashboard refreshes this on every load.
                    $pending = session('admin_pending_bookings', 0);
                @endphp
                @if($pending > 0)<span class="nav-badge">{{ $pending }}</span>@endif
            </a>
            <a href="{{ route('admin.checkouts.index') }}" class="nav-item {{ request()->routeIs('admin.checkouts*') ? 'active' : '' }}">
                <i class="ti ti-package-export"></i> Checkout
            </a>
        </div>
        <div class="sidebar-section">
            <div class="sidebar-section-label">Sistem</div>
            <a href="{{ route('admin.members.index') }}" class="nav-item {{ request()->routeIs('admin.members*') ? 'active' : '' }}">
                <i class="ti ti-users"></i> Anggota
            </a>
        </div>
        @else
        {{-- MEMBER NAV --}}
        <div class="sidebar-section">
            <div class="sidebar-section-label">Menu</div>
            <a href="{{ route('member.dashboard') }}" class="nav-item {{ request()->routeIs('member.dashboard') ? 'active' : '' }}">
                <i class="ti ti-home"></i> Beranda
            </a>
            <a href="{{ route('member.equipment.index') }}" class="nav-item {{ request()->routeIs('member.equipment*') ? 'active' : '' }}">
                <i class="ti ti-camera"></i> Pinjam Alat
            </a>
            <a href="{{ route('member.bookings.index') }}" class="nav-item {{ request()->routeIs('member.bookings*') ? 'active' : '' }}">
                <i class="ti ti-calendar-plus"></i> Booking Ruangan
            </a>
            <a href="{{ route('member.checkouts.index') }}" class="nav-item {{ request()->routeIs('member.checkouts*') ? 'active' : '' }}">
                <i class="ti ti-history"></i> Riwayat Saya
            </a>
        </div>
        @endif
    </div>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div style="min-width:0">
                <div class="user-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
                @csrf
                <button type="submit" class="btn btn-icon btn-secondary" title="Logout" style="border:none;background:none;color:var(--text-3);cursor:pointer">
                    <i class="ti ti-logout" style="font-size:16px"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- TOPBAR --}}
<header class="topbar">
    <button class="hamburger btn btn-icon btn-secondary" onclick="toggleSidebar()">
        <i class="ti ti-menu-2" style="font-size:18px"></i>
    </button>
    <div>
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        @hasSection('breadcrumb')
        <div class="topbar-breadcrumb">@yield('breadcrumb')</div>
        @endif
    </div>
    <div class="topbar-right">
        <span class="text-sm text-muted">{{ now()->format('d M Y') }}</span>
    </div>
</header>

{{-- MAIN --}}
<main class="main">
    <div class="content">
        @if(session('success'))
            <div class="alert alert-success"><i class="ti ti-circle-check"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error"><i class="ti ti-alert-circle"></i> {{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</main>

<div class="toast-container" id="toastContainer"></div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="ti ti-${type==='success'?'check':'alert-circle'}"></i> ${msg}`;
    document.getElementById('toastContainer').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-backdrop.open').forEach(m => m.classList.remove('open'));
});
</script>
@stack('scripts')
</body>
</html>
