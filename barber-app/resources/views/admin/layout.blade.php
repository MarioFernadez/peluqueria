<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin') — Peluquería</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        :root {
            --sidebar-w: 260px;
            --bg: #0f1117;
            --surface: #1a1d27;
            --surface2: #22263a;
            --border: #2e3248;
            --accent: #7c6aff;
            --accent2: #a78bfa;
            --text: #e2e8f0;
            --muted: #64748b;
            --green: #22c55e;
            --red: #ef4444;
            --yellow: #eab308;
        }
        body { background: var(--bg); color: var(--text); margin: 0; min-height: 100vh; }
        .sidebar {
            width: var(--sidebar-w); min-height: 100vh; background: var(--surface);
            border-right: 1px solid var(--border); position: fixed; top: 0; left: 0;
            display: flex; flex-direction: column; z-index: 100;
            transition: transform 0.3s ease;
        }
        .sidebar-brand {
            padding: 1.5rem 1.25rem; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .sidebar-brand .icon {
            width: 36px; height: 36px; background: var(--accent); border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .sidebar-brand h1 { font-size: 1rem; font-weight: 700; margin: 0; color: white; }
        .sidebar-brand p { font-size: 0.7rem; margin: 0; color: var(--muted); }
        .sidebar-nav { padding: 1rem 0.75rem; flex: 1; }
        .nav-section { font-size: 0.65rem; font-weight: 600; color: var(--muted); text-transform: uppercase;
            letter-spacing: 0.08em; padding: 0.5rem 0.5rem 0.25rem; margin-top: 0.5rem; }
        .nav-item {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem;
            border-radius: 8px; color: var(--muted); text-decoration: none; font-size: 0.875rem;
            font-weight: 500; transition: all 0.15s ease; margin-bottom: 2px;
        }
        .nav-item:hover { background: var(--surface2); color: var(--text); }
        .nav-item.active { background: rgba(124,106,255,0.15); color: var(--accent2); }
        .nav-item .icon { font-size: 1rem; width: 20px; text-align: center; }
        .sidebar-footer {
            padding: 1rem 0.75rem; border-top: 1px solid var(--border);
        }
        .main { margin-left: var(--sidebar-w); min-height: 100vh; }
        .topbar {
            height: 60px; background: var(--surface); border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem; position: sticky; top: 0; z-index: 50;
        }
        .topbar h2 { font-size: 1rem; font-weight: 600; margin: 0; color: var(--text); }
        .topbar .badge {
            background: rgba(124,106,255,0.15); color: var(--accent2);
            font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.6rem;
            border-radius: 999px; border: 1px solid rgba(124,106,255,0.3);
        }
        .page-content { padding: 1.5rem; }
        .card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 12px; padding: 1.25rem;
        }
        .card-title { font-size: 0.8rem; font-weight: 600; color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 1rem; }
        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.825rem;
            font-weight: 500; cursor: pointer; border: none; transition: all 0.15s;
            text-decoration: none;
        }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: #6a58e8; }
        .btn-ghost { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
        .btn-ghost:hover { border-color: var(--accent); color: var(--accent2); }
        .btn-danger { background: rgba(239,68,68,0.15); color: var(--red); border: 1px solid rgba(239,68,68,0.3); }
        .btn-danger:hover { background: rgba(239,68,68,0.25); }
        .btn-sm { padding: 0.3rem 0.65rem; font-size: 0.75rem; }
        .badge-status {
            display: inline-flex; align-items: center; gap: 0.3rem;
            font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.55rem;
            border-radius: 999px;
        }
        .badge-pendiente  { background: rgba(234,179,8,0.15); color: var(--yellow); }
        .badge-confirmada { background: rgba(34,197,94,0.15); color: var(--green); }
        .badge-finalizada { background: rgba(100,116,139,0.15); color: var(--muted); }
        .badge-cancelada  { background: rgba(239,68,68,0.15); color: var(--red); }
        .badge-pagado     { background: rgba(34,197,94,0.15); color: var(--green); }
        .badge-pendiente-pay { background: rgba(234,179,8,0.15); color: var(--yellow); }
        .table { width: 100%; border-collapse: collapse; font-size: 0.825rem; }
        .table th { text-align: left; padding: 0.6rem 0.75rem; color: var(--muted);
            font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.05em; border-bottom: 1px solid var(--border); }
        .table td { padding: 0.75rem; border-bottom: 1px solid rgba(46,50,72,0.5); vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: rgba(255,255,255,0.02); }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 500; color: var(--muted); margin-bottom: 0.35rem; }
        .form-control {
            width: 100%; padding: 0.55rem 0.75rem; background: var(--surface2);
            border: 1px solid var(--border); border-radius: 8px; color: var(--text);
            font-size: 0.875rem; outline: none; transition: border-color 0.15s; box-sizing: border-box;
        }
        .form-control:focus { border-color: var(--accent); }
        .form-control option { background: var(--surface2); }
        .grid { display: grid; }
        .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }
        .alert {
            padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.825rem;
            margin-bottom: 1rem;
        }
        .alert-success { background: rgba(34,197,94,0.1); color: var(--green); border: 1px solid rgba(34,197,94,0.2); }
        .alert-error { background: rgba(239,68,68,0.1); color: var(--red); border: 1px solid rgba(239,68,68,0.2); }
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7);
            z-index: 200; align-items: center; justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 14px; padding: 1.5rem; width: 90%; max-width: 480px;
            max-height: 90vh; overflow-y: auto;
        }
        .modal-title { font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 12px; padding: 1.25rem;
            display: flex; flex-direction: column; gap: 0.4rem;
        }
        .stat-card .label { font-size: 0.75rem; font-weight: 500; color: var(--muted); }
        .stat-card .value { font-size: 1.6rem; font-weight: 700; color: white; }
        .stat-card .sub { font-size: 0.75rem; color: var(--muted); }
        .stat-card .icon-wrap {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin-bottom: 0.5rem;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .grid-cols-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-cols-3 { grid-template-columns: 1fr; }
            .grid-cols-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="icon">✂️</div>
        <div>
            <h1>BarberPro</h1>
            <p>Panel de Administración</p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="icon">🏠</span> Dashboard
        </a>
        <a href="{{ route('admin.clients.index') }}" class="nav-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
            <span class="icon">👥</span> Clientes
        </a>

        <div class="nav-section">Gestión</div>
        <a href="{{ route('admin.dashboard') }}#turnos" class="nav-item {{ request()->is('admin') ? '' : '' }}">
            <span class="icon">📅</span> Turnos
        </a>
        <a href="{{ route('admin.dashboard') }}#memberships" class="nav-item">
            <span class="icon">💳</span> Membresías
        </a>
        <a href="{{ route('admin.dashboard') }}#barberos" class="nav-item">
            <span class="icon">✂️</span> Barberos
        </a>
        <a href="{{ route('admin.dashboard') }}#servicios" class="nav-item">
            <span class="icon">📋</span> Servicios
        </a>

        <div class="nav-section">Análisis</div>
        <a href="{{ route('admin.finance.index') }}" class="nav-item {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
            <span class="icon">💰</span> Finanzas
        </a>
        <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <span class="icon">📊</span> Reportes
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="{{ route('admin.logout') }}" class="nav-item btn-danger" style="border-radius:8px;">
            <span class="icon">🚪</span> Cerrar sesión
        </a>
    </div>
</aside>

<!-- Main -->
<div class="main">
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:1rem;">
            <button onclick="document.getElementById('sidebar').classList.toggle('open')"
                style="display:none;background:none;border:none;color:var(--text);font-size:1.25rem;cursor:pointer;" id="menu-btn">☰</button>
            <h2>@yield('page-title', 'Dashboard')</h2>
        </div>
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <span class="badge">{{ auth()->user()->name ?? 'Admin' }}</span>
        </div>
    </header>

    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error') || $errors->any())
            <div class="alert alert-error">
                ❌ {{ session('error') ?? $errors->first() }}
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script>
    // Mostrar botón menú en mobile
    if (window.innerWidth <= 768) {
        document.getElementById('menu-btn').style.display = 'block';
    }
    window.addEventListener('resize', () => {
        document.getElementById('menu-btn').style.display = window.innerWidth <= 768 ? 'block' : 'none';
    });

    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    // Cerrar modal al click fuera
    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
    });
</script>
@stack('scripts')
</body>
</html>
