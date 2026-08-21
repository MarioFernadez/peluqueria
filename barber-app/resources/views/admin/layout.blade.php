<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d0f14">
    <title>@yield('title', 'Panel Admin') — BarberPro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        * { font-family: 'Inter', sans-serif; }

        /* ── Tokens de color: OSCURO ── */
        :root, [data-theme="dark"] {
            --sidebar-w: 256px;
            --bottomnav-h: 64px;
            --bg: #0d0f14;
            --surface: #141720;
            --surface2: #1c2030;
            --surface3: #242840;
            --border: rgba(255,255,255,0.07);
            --border2: rgba(255,255,255,0.12);
            --accent: #6366f1;
            --accent-hover: #4f51d9;
            --accent2: #a5b4fc;
            --accent-bg: rgba(99,102,241,0.12);
            --text: #f1f5f9;
            --text2: #94a3b8;
            --muted: #64748b;
            --green: #22c55e;
            --red: #f87171;
            --yellow: #fbbf24;
            --success: #22c55e;
            --logo-bg: linear-gradient(135deg, #6366f1, #4f46e5);
            --shadow: 0 1px 3px rgba(0,0,0,0.4);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.5);
        }

        /* ── Tokens de color: CLARO ── */
        [data-theme="light"] {
            --bg: #f4f6f8;
            --surface: #ffffff;
            --surface2: #f8fafc;
            --surface3: #e2e8f0;
            --border: rgba(0,0,0,0.05);
            --border2: rgba(0,0,0,0.1);
            --accent: #4f46e5;
            --accent-hover: #4338ca;
            --accent2: #6366f1;
            --accent-bg: rgba(79,70,229,0.08);
            --text: #111827;
            --text2: #4b5563;
            --muted: #9ca3af;
            --green: #059669;
            --red: #dc2626;
            --yellow: #d97706;
            --success: #059669;
            --logo-bg: linear-gradient(135deg, #4f46e5, #3730a3);
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.03), 0 2px 4px -2px rgba(0,0,0,0.03);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.04), 0 4px 6px -4px rgba(0,0,0,0.02);
        }

        html, body { height: 100%; }
        body {
            background: var(--bg);
            color: var(--text);
            margin: 0;
            min-height: 100vh;
            transition: background 0.25s ease, color 0.25s ease;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            position: fixed; top: 0; left: 0;
            display: flex; flex-direction: column;
            z-index: 300;
            box-shadow: var(--shadow);
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1), background 0.25s ease, box-shadow 0.25s ease;
        }

        /* Overlay for sidebar on mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(3px);
            z-index: 299;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        .sidebar-brand {
            padding: 1.25rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .sidebar-logo {
            width: 36px; height: 36px;
            background: var(--logo-bg);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(99,102,241,0.35);
        }
        .sidebar-logo svg { color: white; }
        .sidebar-brand-text h1 { font-size: 0.95rem; font-weight: 700; color: var(--text); letter-spacing: -0.01em; }
        .sidebar-brand-text p { font-size: 0.68rem; color: var(--muted); margin-top: 1px; }

        /* Close button inside sidebar on mobile */
        .sidebar-close-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text2);
            cursor: pointer;
            padding: 4px;
            margin-left: auto;
        }

        .sidebar-nav { padding: 1rem 0.75rem; flex: 1; overflow-y: auto; }
        .nav-section {
            font-size: 0.62rem; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.1em;
            padding: 0.75rem 0.5rem 0.3rem; margin-top: 0.25rem;
        }
        .nav-item {
            display: flex; align-items: center; gap: 0.7rem;
            padding: 0.6rem 0.7rem;
            border-radius: 8px;
            color: var(--text2);
            text-decoration: none;
            font-size: 0.85rem; font-weight: 500;
            transition: all 0.15s ease;
            margin-bottom: 2px;
        }
        .nav-item:hover { background: var(--surface2); color: var(--text); }
        .nav-item.active {
            background: var(--accent-bg);
            color: var(--accent2);
        }
        .nav-item .nav-icon {
            width: 18px; height: 18px;
            flex-shrink: 0;
            opacity: 0.75;
            transition: opacity 0.15s;
        }
        .nav-item:hover .nav-icon,
        .nav-item.active .nav-icon { opacity: 1; }
        .nav-item.active .nav-icon { color: var(--accent2); }

        .sidebar-footer {
            padding: 1rem 0.75rem;
            border-top: 1px solid var(--border);
        }
        .sidebar-footer .nav-item {
            color: var(--red);
        }
        .sidebar-footer .nav-item:hover {
            background: rgba(248,113,113,0.08);
            color: var(--red);
        }

        /* ── Main area ── */
        .main { margin-left: var(--sidebar-w); min-height: 100vh; }

        .topbar {
            height: 58px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky; top: 0; z-index: 50;
            box-shadow: var(--shadow);
            transition: background 0.25s ease, box-shadow 0.25s ease;
        }
        .topbar-left { display: flex; align-items: center; gap: 1rem; }
        .topbar h2 { font-size: 0.9rem; font-weight: 600; color: var(--text); }
        .topbar-right { display: flex; align-items: center; gap: 0.75rem; }

        .topbar-badge {
            background: var(--accent-bg);
            color: var(--accent2);
            font-size: 0.7rem; font-weight: 600;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
            border: 1px solid rgba(99,102,241,0.25);
            white-space: nowrap;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Hamburger button ── */
        #menu-btn {
            display: none;
            background: none;
            border: 1px solid var(--border2);
            color: var(--text);
            width: 36px; height: 36px;
            border-radius: 8px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background 0.15s;
        }
        #menu-btn:hover { background: var(--surface2); }

        /* ── Theme toggle button ── */
        .theme-toggle {
            width: 34px; height: 34px;
            border-radius: 8px;
            border: 1px solid var(--border2);
            background: var(--surface2);
            color: var(--text2);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s ease;
        }
        .theme-toggle:hover { background: var(--surface3); color: var(--text); }
        .theme-toggle svg { width: 16px; height: 16px; }
        .icon-sun { display: none; }
        .icon-moon { display: block; }
        [data-theme="light"] .icon-sun { display: block; }
        [data-theme="light"] .icon-moon { display: none; }

        /* ── Page content ── */
        .page-content { padding: 1.5rem; }

        /* ── Cards ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px; padding: 1.25rem;
            box-shadow: var(--shadow);
            transition: background 0.25s ease, box-shadow 0.25s ease;
        }
        .card-title {
            font-size: 0.75rem; font-weight: 600; color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 1rem;
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            border-radius: 7px;
            font-size: 0.8rem; font-weight: 600;
            cursor: pointer; border: none;
            transition: all 0.15s;
            text-decoration: none;
            letter-spacing: 0.01em;
        }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-hover); transform: translateY(-1px); }
        .btn-ghost { background: var(--surface2); color: var(--text); border: 1px solid var(--border2); }
        .btn-ghost:hover { border-color: var(--accent); color: var(--accent2); }
        .btn-danger { background: rgba(248,113,113,0.1); color: var(--red); border: 1px solid rgba(248,113,113,0.25); }
        .btn-danger:hover { background: rgba(248,113,113,0.2); }
        .btn-sm { padding: 0.3rem 0.6rem; font-size: 0.73rem; }

        /* ── Status badges ── */
        .badge-status {
            display: inline-flex; align-items: center; gap: 0.25rem;
            font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.55rem;
            border-radius: 999px; letter-spacing: 0.02em;
        }
        .badge-pendiente   { background: rgba(251,191,36,0.12);  color: var(--yellow); border: 1px solid rgba(251,191,36,0.2); }
        .badge-confirmada  { background: rgba(34,197,94,0.12);   color: var(--green);  border: 1px solid rgba(34,197,94,0.2); }
        .badge-finalizada  { background: rgba(100,116,139,0.12); color: var(--muted);  border: 1px solid rgba(100,116,139,0.2); }
        .badge-cancelada   { background: rgba(248,113,113,0.12); color: var(--red);    border: 1px solid rgba(248,113,113,0.2); }
        .badge-pagado      { background: rgba(34,197,94,0.12);   color: var(--green);  border: 1px solid rgba(34,197,94,0.2); }
        .badge-pendiente-pay { background: rgba(251,191,36,0.12); color: var(--yellow); border: 1px solid rgba(251,191,36,0.2); }

        /* ── Table ── */
        .table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
        .table th {
            text-align: left; padding: 0.55rem 0.75rem;
            color: var(--muted); font-size: 0.68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.06em;
            border-bottom: 1px solid var(--border);
        }
        .table td {
            padding: 0.7rem 0.75rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .table tr:last-child td { border-bottom: none; }
        .table tbody tr:hover td { background: var(--surface2); }

        /* ── Responsive table wrapper ── */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ── Mobile cards for tables ── */
        .mobile-card-list { display: none; }
        .mobile-card-item {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.9rem 1rem;
            margin-bottom: 0.75rem;
        }
        .mobile-card-item:last-child { margin-bottom: 0; }
        .mobile-card-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.4rem;
            gap: 0.5rem;
        }
        .mobile-card-row:last-child { margin-bottom: 0; }
        .mobile-card-label {
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            flex-shrink: 0;
        }
        .mobile-card-value {
            font-size: 0.82rem;
            color: var(--text);
            text-align: right;
        }

        /* ── Forms ── */
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: 0.78rem; font-weight: 600; color: var(--text2); margin-bottom: 0.35rem; }
        .form-control {
            width: 100%; padding: 0.55rem 0.75rem;
            background: var(--surface2);
            border: 1px solid var(--border2);
            border-radius: 7px; color: var(--text);
            font-size: 0.85rem; outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            box-sizing: border-box;
            /* Prevent zoom on iOS */
            font-size: max(16px, 0.85rem);
        }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
        .form-control option { background: var(--surface2); }

        /* ── Grid ── */
        .grid { display: grid; }
        .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }

        /* ── Alerts ── */
        .alert {
            padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.82rem;
            margin-bottom: 1rem; display: flex; align-items: center; gap: 0.6rem;
        }
        .alert-success { background: rgba(34,197,94,0.08); color: var(--green); border: 1px solid rgba(34,197,94,0.2); }
        .alert-error { background: rgba(248,113,113,0.08); color: var(--red); border: 1px solid rgba(248,113,113,0.2); }

        /* ── Modals ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.65); backdrop-filter: blur(4px);
            z-index: 500; align-items: flex-end; justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--surface); border: 1px solid var(--border2);
            border-radius: 16px; padding: 1.5rem;
            width: 90%; max-width: 480px;
            max-height: 90vh; overflow-y: auto;
            box-shadow: var(--shadow-lg);
            margin-bottom: 1.5rem;
        }
        .modal-title { font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text); }

        /* ── Stat cards ── */
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 12px; padding: 1.25rem;
            display: flex; flex-direction: column; gap: 0.35rem;
            transition: background 0.25s ease;
        }
        .stat-card .label { font-size: 0.73rem; font-weight: 600; color: var(--muted); }
        .stat-card .value { font-size: 1.45rem; font-weight: 700; color: var(--text); line-height: 1.2; }
        .stat-card .sub { font-size: 0.72rem; color: var(--muted); }
        .stat-card .icon-wrap {
            width: 38px; height: 38px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 0.4rem;
            font-size: 1.1rem;
        }

        /* ── Bottom Nav Bar (mobile only) ── */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: var(--bottomnav-h);
            background: var(--surface);
            border-top: 1px solid var(--border);
            z-index: 200;
            padding: 0 0.5rem;
            padding-bottom: env(safe-area-inset-bottom);
            box-shadow: 0 -4px 16px rgba(0,0,0,0.25);
        }
        .bottom-nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-around;
            height: 100%;
        }
        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            text-decoration: none;
            color: var(--muted);
            font-size: 0.6rem;
            font-weight: 600;
            padding: 0.4rem 0.5rem;
            border-radius: 10px;
            transition: color 0.15s, background 0.15s;
            min-width: 52px;
            text-align: center;
        }
        .bottom-nav-item svg {
            width: 22px; height: 22px;
            stroke-width: 1.8;
        }
        .bottom-nav-item.active {
            color: var(--accent2);
        }
        .bottom-nav-item.active svg {
            filter: drop-shadow(0 0 6px rgba(99,102,241,0.5));
        }
        /* Special center button */
        .bottom-nav-center {
            position: relative;
        }
        .bottom-nav-center .bnav-icon-wrap {
            width: 46px; height: 46px;
            background: var(--accent);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(99,102,241,0.5);
            margin-bottom: 2px;
        }
        .bottom-nav-center svg { color: white; }
        .bottom-nav-center span { color: var(--accent2); font-size: 0.6rem; font-weight: 700; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            /* Sidebar becomes drawer */
            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: var(--shadow-lg);
            }
            .sidebar-close-btn { display: flex; }

            /* Main fills full width */
            .main { margin-left: 0; }

            /* Topbar mobile adjustments */
            .topbar { padding: 0 1rem; }
            #menu-btn { display: flex; }
            .topbar h2 {
                font-size: 0.85rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 160px;
            }
            .topbar-badge { display: none; }

            /* Show bottom nav */
            .bottom-nav { display: block; }

            /* Push content above bottom nav */
            .page-content {
                padding: 1rem;
                padding-bottom: calc(var(--bottomnav-h) + 1.5rem + env(safe-area-inset-bottom));
            }

            /* Grids collapse */
            .grid-cols-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-cols-3 { grid-template-columns: 1fr; }
            .grid-cols-2 { grid-template-columns: 1fr; }

            /* Dashboard two-col layout becomes single col */
            .dashboard-main-row {
                grid-template-columns: 1fr !important;
            }

            /* Modals slide up from bottom on mobile */
            .modal-overlay { align-items: flex-end; }
            .modal {
                width: 100%;
                max-width: 100%;
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
                margin-bottom: 0;
                border-bottom: none;
                max-height: 85vh;
            }

            /* Stat cards smaller on mobile */
            .stat-card { padding: 1rem; }
            .stat-card .value { font-size: 1.25rem; }

            /* Card padding smaller */
            .card { padding: 1rem; }

            /* Grid 2 col en modales en móvil */
            .modal .grid-cols-2 { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            /* Extra small screens */
            .grid-cols-4 { grid-template-columns: repeat(2, 1fr); }
            .stat-card .icon-wrap { width: 32px; height: 32px; }
            .page-content { padding: 0.75rem; }
            .topbar h2 { max-width: 110px; }
        }

        @media (max-width: 360px) {
            /* Very small screens */
            .bottom-nav-item { min-width: 44px; padding: 0.3rem 0.35rem; font-size: 0.55rem; }
            .bottom-nav-item svg { width: 20px; height: 20px; }
            .page-content { padding: 0.5rem; }
            .topbar { padding: 0 0.6rem; }
            .topbar h2 { max-width: 90px; }
        }

        /* Landscape orientation en móvil: comprimir bottom nav */
        @media (max-height: 500px) and (orientation: landscape) {
            .bottom-nav { height: 52px; }
            .bottom-nav-item { font-size: 0; /* ocultar texto, solo íconos */ padding: 0.3rem; }
            .bottom-nav-center .bnav-icon-wrap { width: 38px; height: 38px; }
            .page-content { padding-bottom: calc(52px + 0.5rem + env(safe-area-inset-bottom)); }
        }
    </style>
</head>
<body>

<!-- Sidebar overlay (for mobile) -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-logo">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                <line x1="20" y1="4" x2="8.12" y2="15.88"/>
                <line x1="14.47" y1="14.48" x2="20" y2="20"/>
                <line x1="8.12" y1="8.12" x2="12" y2="12"/>
            </svg>
        </div>
        <div class="sidebar-brand-text">
            <h1>BarberPro</h1>
            <p>Panel de Administración</p>
        </div>
        <button class="sidebar-close-btn" onclick="closeSidebar()" aria-label="Cerrar menú">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" onclick="closeSidebar()">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard
        </a>

        <div class="nav-section">Operaciones</div>
        <a href="{{ route('admin.dashboard') }}#turnos" class="nav-item" onclick="closeSidebar()">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Turnos del Día
        </a>
        <a href="{{ route('admin.clients.index') }}" class="nav-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}" onclick="closeSidebar()">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Clientes
        </a>

        <div class="nav-section">Catálogo</div>
        <a href="{{ route('admin.barbers.index') }}" class="nav-item {{ request()->routeIs('admin.barbers.*') ? 'active' : '' }}" onclick="closeSidebar()">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                <line x1="20" y1="4" x2="8.12" y2="15.88"/>
                <line x1="14.47" y1="14.48" x2="20" y2="20"/>
                <line x1="8.12" y1="8.12" x2="12" y2="12"/>
            </svg>
            Barberos
        </a>
        <a href="{{ route('admin.services.index') }}" class="nav-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" onclick="closeSidebar()">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            Servicios
        </a>
        <a href="{{ route('admin.memberships.index') }}" class="nav-item {{ request()->routeIs('admin.memberships.*') ? 'active' : '' }}" onclick="closeSidebar()">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            Membresías
        </a>

        <div class="nav-section">Análisis</div>
        <a href="{{ route('admin.finance.index') }}" class="nav-item {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}" onclick="closeSidebar()">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            Finanzas
        </a>
        <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" onclick="closeSidebar()">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
            </svg>
            Reportes
        </a>

        <div class="nav-section">Configuración</div>
        <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" onclick="closeSidebar()">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            Apariencia
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="{{ route('admin.logout') }}" class="nav-item">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Cerrar sesión
        </a>
    </div>
</aside>

<!-- Main -->
<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <button onclick="openSidebar()" id="menu-btn" aria-label="Abrir menú">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <h2>@yield('page-title', 'Dashboard')</h2>
        </div>
        <div class="topbar-right">
            <!-- Botón de notificaciones -->
            <button id="enable-notifications-btn" class="btn btn-ghost btn-sm" style="margin-right: 0.5rem;" title="Activar notificaciones" onclick="enableNotifications()">
                🔔 Activar Alertas
            </button>
            
            <!-- Botón modo oscuro/claro -->
            <button class="theme-toggle" id="themeToggle" title="Cambiar tema" onclick="toggleTheme()">
                <!-- Luna (visible en modo oscuro) -->
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                <!-- Sol (visible en modo claro) -->
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
            </button>
            <span class="topbar-badge">{{ auth()->user()->name ?? 'Admin' }}</span>
        </div>
    </header>

    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error') || $errors->any())
            <div class="alert alert-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                {{ session('error') ?? $errors->first() }}
            </div>
        @endif

        @yield('content')
    </div>
</div>

<!-- ── Bottom Navigation Bar (Mobile) ── -->
<nav class="bottom-nav" aria-label="Navegación principal">
    <div class="bottom-nav-inner">
        <a href="{{ route('admin.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            <span>Inicio</span>
        </a>

        <a href="{{ route('admin.clients.index') }}" class="bottom-nav-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
            <span>Clientes</span>
        </a>

        <a href="{{ route('admin.finance.index') }}" class="bottom-nav-item bottom-nav-center {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
            <div class="bnav-icon-wrap">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <span>Finanzas</span>
        </a>

        <a href="{{ route('admin.barbers.index') }}" class="bottom-nav-item {{ request()->routeIs('admin.barbers.*') || request()->routeIs('admin.services.*') || request()->routeIs('admin.memberships.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                <line x1="20" y1="4" x2="8.12" y2="15.88"/>
                <line x1="14.47" y1="14.48" x2="20" y2="20"/>
                <line x1="8.12" y1="8.12" x2="12" y2="12"/>
            </svg>
            <span>Catálogo</span>
        </a>

        <a href="{{ route('admin.settings.index') }}" class="bottom-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            <span>Config</span>
        </a>
    </div>
</nav>

<script>
    // ── Sidebar mobile control ──
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebar-overlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Close sidebar with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });

    // ── Modals ──
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
    });

    // ── Theme toggle (dark / light) ──
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('barberpro_theme', theme);
        // Update meta theme-color for mobile browsers
        document.querySelector('meta[name="theme-color"]').setAttribute(
            'content', theme === 'dark' ? '#0d0f14' : '#f4f6f8'
        );
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    }

    // Restore saved preference on load
    (function() {
        const saved = localStorage.getItem('barberpro_theme') || 'dark';
        applyTheme(saved);
        
        // Check if notifications are already granted
        if (window.Notification && Notification.permission === 'granted') {
            const btn = document.getElementById('enable-notifications-btn');
            if(btn) {
                btn.textContent = '🔔 Alertas Activas';
                btn.style.opacity = '0.5';
                btn.disabled = true;
            }
        }
    })();

    // ── Web Push Notifications ──
    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);

        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    async function enableNotifications() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            alert('Tu navegador no soporta notificaciones push.');
            return;
        }
        
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            alert('Permiso denegado para enviar notificaciones.');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            const vapidPublicKey = '{{ config('webpush.vapid.public_key') }}';
            const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: convertedVapidKey
            });

            // Enviar al servidor
            const token = document.querySelector('meta[name="csrf-token"]').content;
            await fetch('/admin/push-subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(subscription)
            });

            const btn = document.getElementById('enable-notifications-btn');
            if(btn) {
                btn.textContent = '🔔 Alertas Activas';
                btn.style.opacity = '0.5';
                btn.disabled = true;
            }
            alert('¡Notificaciones activadas con éxito!');

        } catch (error) {
            console.error('Error suscribiendo:', error);
            alert('Hubo un error al activar las notificaciones.');
        }
    }
</script>
@stack('scripts')
</body>
</html>
