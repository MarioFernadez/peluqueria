@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<style>
    /* ── Widget Customizer Panel ── */
    .customizer-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        z-index: 400;
    }
    .customizer-overlay.open { display: block; }

    .customizer-panel {
        position: fixed;
        top: 0; right: 0;
        width: 300px;
        height: 100vh;
        background: var(--surface);
        border-left: 1px solid var(--border2);
        box-shadow: -8px 0 32px rgba(0,0,0,0.4);
        z-index: 401;
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .customizer-panel.open { transform: translateX(0); }

    .customizer-header {
        padding: 1.25rem 1.25rem 1rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .customizer-header h3 {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text);
    }
    .customizer-close {
        background: none;
        border: none;
        color: var(--muted);
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        transition: background 0.15s, color 0.15s;
    }
    .customizer-close:hover { background: var(--surface2); color: var(--text); }

    .customizer-body {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 1.25rem;
    }
    .customizer-body p {
        font-size: 0.75rem;
        color: var(--muted);
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .widget-toggle-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border);
    }
    .widget-toggle-item:last-child { border-bottom: none; }
    .widget-toggle-label {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.83rem;
        font-weight: 500;
        color: var(--text2);
    }
    .widget-toggle-label .wt-icon { font-size: 1rem; }

    /* Toggle switch */
    .toggle-switch {
        position: relative;
        width: 38px;
        height: 22px;
        flex-shrink: 0;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }
    .toggle-track {
        position: absolute;
        inset: 0;
        background: var(--surface3);
        border-radius: 999px;
        cursor: pointer;
        transition: background 0.2s;
        border: 1px solid var(--border2);
    }
    .toggle-track::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 16px;
        height: 16px;
        background: var(--muted);
        border-radius: 50%;
        transition: transform 0.2s, background 0.2s;
    }
    .toggle-switch input:checked + .toggle-track {
        background: var(--accent);
        border-color: var(--accent);
    }
    .toggle-switch input:checked + .toggle-track::after {
        transform: translateX(16px);
        background: white;
    }

    /* ── Customize button in topbar-like area ── */
    .dashboard-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .dashboard-greeting {
        line-height: 1.3;
    }
    .dashboard-greeting .greeting-text {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text);
    }
    .dashboard-greeting .greeting-sub {
        font-size: 0.78rem;
        color: var(--muted);
        margin-top: 2px;
    }
    .btn-customize {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.85rem;
        background: var(--surface2);
        border: 1px solid var(--border2);
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text2);
        cursor: pointer;
        transition: all 0.15s;
    }
    .btn-customize:hover {
        border-color: var(--accent);
        color: var(--accent2);
        background: var(--accent-bg);
    }

    /* ── Widgets grid ── */
    .widgets-grid {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* ── Stats row ── */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    /* ── Main row ── */
    .dashboard-main-row {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.5rem;
    }

    /* ── Agenda widget ── */
    .agenda-scroll {
        max-height: 420px;
        overflow-y: auto;
    }

    /* Appointment cards */
    .appt-cards-mobile { display: none; }
    .appt-card {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 0.9rem 1rem;
        margin-bottom: 0.65rem;
    }
    .appt-card:last-child { margin-bottom: 0; }
    .appt-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.6rem;
        gap: 0.5rem;
    }
    .appt-card-time { font-size: 1rem; font-weight: 700; color: var(--accent2); }
    .appt-card-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.4rem 0.75rem;
        margin-bottom: 0.65rem;
    }
    .appt-card-field-label {
        font-size: 0.63rem; font-weight: 700; color: var(--muted);
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .appt-card-field-value { font-size: 0.82rem; color: var(--text); margin-top: 1px; }
    .appt-card-footer {
        display: flex; align-items: center; justify-content: space-between;
        gap: 0.5rem; padding-top: 0.6rem;
        border-top: 1px solid var(--border);
    }
    .appt-free-slot { background: rgba(34,197,94,0.04); border-color: rgba(34,197,94,0.12); }

    /* ── Sidebar widgets ── */
    .sidebar-widgets {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* ── Quick Access cards (Catálogo) ── */
    .quick-access-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    .quick-card {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.1rem 1rem;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        text-align: center;
        transition: all 0.18s ease;
    }
    .quick-card:hover {
        border-color: var(--accent);
        background: var(--accent-bg);
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(99,102,241,0.15);
    }
    .quick-card-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
    }
    .quick-card-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text2);
    }
    .quick-card-count {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text);
        line-height: 1;
    }

    /* ── Widget visibility ── */
    .widget-section { display: block; }
    .widget-section.hidden { display: none !important; }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .dashboard-main-row { grid-template-columns: 1fr; }
        .stats-row { grid-template-columns: repeat(2, 1fr); }
        .quick-access-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 600px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
        .appt-table-desktop { display: none !important; }
        .appt-cards-mobile { display: block !important; }
        .quick-access-grid { grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
        .quick-card { padding: 0.75rem 0.5rem; }
        .dashboard-header { margin-bottom: 1rem; }
        .greeting-text { font-size: 1rem !important; }
        .customizer-panel { width: 100%; }
    }
    @media (max-width: 400px) {
        .stats-row { grid-template-columns: 1fr 1fr; }
        .stat-card .value { font-size: 1.1rem; }
    }
</style>

{{-- Customizer Panel Overlay --}}
<div class="customizer-overlay" id="customizer-overlay" onclick="closeCustomizer()"></div>

{{-- Customizer Panel --}}
<div class="customizer-panel" id="customizer-panel">
    <div class="customizer-header">
        <h3>Personalizar Dashboard</h3>
        <button class="customizer-close" onclick="closeCustomizer()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
    <div class="customizer-body">
        <p>Activa o desactiva los widgets que querés ver en tu dashboard. Los cambios se guardan automáticamente.</p>

        <div class="widget-toggle-item">
            <div class="widget-toggle-label">
                Métricas del día
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="toggle-stats-day" checked onchange="toggleWidget('widget-stats-day', this.checked)">
                <span class="toggle-track"></span>
            </label>
        </div>

        <div class="widget-toggle-item">
            <div class="widget-toggle-label">
                Métricas del mes
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="toggle-stats-month" checked onchange="toggleWidget('widget-stats-month', this.checked)">
                <span class="toggle-track"></span>
            </label>
        </div>

        <div class="widget-toggle-item">
            <div class="widget-toggle-label">
                Agenda de hoy
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="toggle-agenda" checked onchange="toggleWidget('widget-agenda', this.checked)">
                <span class="toggle-track"></span>
            </label>
        </div>

        <div class="widget-toggle-item">
            <div class="widget-toggle-label">
                Vencimientos próximos
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="toggle-expiring" checked onchange="toggleWidget('widget-expiring', this.checked)">
                <span class="toggle-track"></span>
            </label>
        </div>

        <div class="widget-toggle-item">
            <div class="widget-toggle-label">
                Barbero del mes
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="toggle-top-barber" checked onchange="toggleWidget('widget-top-barber', this.checked)">
                <span class="toggle-track"></span>
            </label>
        </div>

        <div class="widget-toggle-item">
            <div class="widget-toggle-label">
                Servicios populares
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="toggle-top-services" checked onchange="toggleWidget('widget-top-services', this.checked)">
                <span class="toggle-track"></span>
            </label>
        </div>

        <div class="widget-toggle-item">
            <div class="widget-toggle-label">
                Accesos rápidos
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="toggle-quick-access" checked onchange="toggleWidget('widget-quick-access', this.checked)">
                <span class="toggle-track"></span>
            </label>
        </div>
    </div>
</div>

{{-- ── Dashboard Header ── --}}
<div class="dashboard-header">
    <div class="dashboard-greeting">
        <div class="greeting-text">Hola, {{ auth()->user()->name ?? 'Administrador' }}</div>
        <div class="greeting-sub">{{ now()->translatedFormat('l, d \d\e F Y') }}</div>
    </div>
    <button class="btn-customize" onclick="openCustomizer()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
        </svg>
        Personalizar
    </button>
</div>

<div class="widgets-grid">

    {{-- ── Widget: Métricas del día ── --}}
    <div class="widget-section" id="widget-stats-day">
        <div style="font-size:0.65rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.65rem;">Métricas del día · {{ now()->format('d/m/Y') }}</div>
        <div class="stats-row">
            <div class="stat-card">
                <div class="icon-wrap" style="background:rgba(124,106,255,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="label">Turnos hoy</div>
                <div class="value">{{ $todayCount }}</div>
                <div class="sub">{{ now()->translatedFormat('l') }}</div>
            </div>
            <div class="stat-card">
                <div class="icon-wrap" style="background:rgba(34,197,94,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div class="label">Ganancia del día</div>
                <div class="value">${{ number_format($todayRevenue, 0, ',', '.') }}</div>
                <div class="sub">Cobrado hoy</div>
            </div>
            <div class="stat-card">
                <div class="icon-wrap" style="background:rgba(59,130,246,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="label">Clientes del día</div>
                <div class="value">{{ $todayAppointments->pluck('customer_name')->merge($todayAppointments->pluck('client.name')->filter())->unique()->count() }}</div>
                <div class="sub">Atendidos o por atender</div>
            </div>
            <div class="stat-card">
                <div class="icon-wrap" style="background:rgba(234,179,8,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div class="label">Membresías activas</div>
                <div class="value">{{ $activeMemberships }}</div>
                <div class="sub">{{ $newClientsMonth }} nuevos este mes</div>
            </div>
        </div>
    </div>

    {{-- ── Widget: Métricas del mes ── --}}
    <div class="widget-section" id="widget-stats-month">
        <div style="font-size:0.65rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.65rem;">Métricas del mes · {{ now()->translatedFormat('F Y') }}</div>
        <div class="stats-row">
            <div class="stat-card">
                <div class="icon-wrap" style="background:rgba(34,197,94,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                </div>
                <div class="label">Ganancia del mes</div>
                <div class="value">${{ number_format($monthRevenue, 0, ',', '.') }}</div>
                <div class="sub">Total cobrado</div>
            </div>
            <div class="stat-card">
                <div class="icon-wrap" style="background:rgba(99,102,241,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                </div>
                <div class="label">Clientes nuevos</div>
                <div class="value">{{ $newClientsMonth }}</div>
                <div class="sub">Este mes</div>
            </div>
            <div class="stat-card">
                <div class="icon-wrap" style="background:rgba(248,113,113,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div class="label">Total de turnos</div>
                <div class="value">{{ $totalTurnos }}</div>
                <div class="sub">Histórico total</div>
            </div>
            <div class="stat-card">
                <div class="icon-wrap" style="background:rgba(251,191,36,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div class="label">Caja total</div>
                <div class="value">${{ number_format($totalCaja, 0, ',', '.') }}</div>
                <div class="sub">Acumulado histórico</div>
            </div>
        </div>
    </div>

    {{-- ── Widget: Agenda + Panel lateral ── --}}
    <div class="widget-section" id="widget-agenda">
        <div class="dashboard-main-row">

            {{-- Agenda de hoy --}}
            <div class="card" id="turnos">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <div class="card-title" style="margin:0;">Turnos de hoy</div>
                    <button class="btn btn-primary btn-sm" onclick="openModal('modal-new-appointment')">+ Nuevo turno</button>
                </div>

                {{-- Desktop table --}}
                <div class="appt-table-desktop table-responsive agenda-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>WhatsApp</th>
                                <th>Barbero</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($agenda as $slot)
                                @if($slot['appointments']->isEmpty())
                                    <tr>
                                        <td style="font-weight:600;color:var(--muted);">{{ $slot['time'] }}</td>
                                        <td style="color:var(--success);font-weight:500;">Libre</td>
                                        <td>—</td><td>—</td><td>—</td><td>—</td><td></td>
                                    </tr>
                                @else
                                    @foreach($slot['appointments'] as $appt)
                                    <tr>
                                        <td style="font-weight:600;color:var(--accent2);">
                                            {{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}
                                        </td>
                                        <td>
                                            @if($appt->client)
                                                <a href="{{ route('admin.clients.show', $appt->client) }}" style="color:var(--text);text-decoration:none;font-weight:500;">{{ $appt->client->name }}</a>
                                            @else
                                                <span style="color:var(--muted);">{{ $appt->customer_name }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php $phone = $appt->client ? $appt->client->phone : $appt->customer_phone; @endphp
                                            @if($phone)
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank" style="color:#25D366;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                                    WA
                                                </a>
                                            @else
                                                <span style="color:var(--muted);font-size:0.8rem;">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $appt->barber->name ?? '—' }}</td>
                                        <td>{{ $appt->service->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge-status badge-{{ strtolower($appt->status) }}">{{ $appt->status }}</span>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.appointment.update', $appt) }}" style="display:inline;">
                                                @csrf
                                                <select name="status" onchange="this.form.submit()" class="form-control" style="padding:0.2rem 0.4rem;font-size:0.7rem;width:auto;">
                                                    @foreach(['Pendiente','Confirmada','Finalizada','Cancelada'] as $st)
                                                        <option value="{{ $st }}" {{ $appt->status == $st ? 'selected' : '' }}>{{ $st }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="appt-cards-mobile">
                    @foreach($agenda as $slot)
                        @if($slot['appointments']->isEmpty())
                            <div class="appt-card appt-free-slot" style="background:rgba(34,197,94,0.04);border-color:rgba(34,197,94,0.12);">
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="font-weight:700;color:var(--muted);">{{ $slot['time'] }}</span>
                                    <span style="color:var(--success);font-size:0.8rem;font-weight:500;">✓ Libre</span>
                                </div>
                            </div>
                        @else
                            @foreach($slot['appointments'] as $appt)
                            <div class="appt-card">
                                <div class="appt-card-header">
                                    <span class="appt-card-time">{{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}</span>
                                    <span class="badge-status badge-{{ strtolower($appt->status) }}">{{ $appt->status }}</span>
                                </div>
                                <div class="appt-card-body">
                                    <div><div class="appt-card-field-label">Cliente</div><div class="appt-card-field-value" style="font-weight:600;">
                                        @if($appt->client)
                                            <a href="{{ route('admin.clients.show', $appt->client) }}" style="color:var(--text);text-decoration:none;">{{ $appt->client->name }}</a>
                                        @else {{ $appt->customer_name }} @endif
                                    </div></div>
                                    <div><div class="appt-card-field-label">Barbero</div><div class="appt-card-field-value">{{ $appt->barber->name ?? '—' }}</div></div>
                                    <div><div class="appt-card-field-label">Servicio</div><div class="appt-card-field-value">{{ $appt->service->name ?? '—' }}</div></div>
                                    @php $phone = $appt->client ? $appt->client->phone : $appt->customer_phone; @endphp
                                    @if($phone)
                                    <div><div class="appt-card-field-label">Contacto</div>
                                        <div class="appt-card-field-value"><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank" style="color:#25D366;font-weight:600;">📱 WA</a></div></div>
                                    @endif
                                </div>
                                <div class="appt-card-footer">
                                    <form method="POST" action="{{ route('admin.appointment.update', $appt) }}" style="flex:1;">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="form-control" style="width:100%;font-size:0.8rem;">
                                            @foreach(['Pendiente','Confirmada','Finalizada','Cancelada'] as $st)
                                                <option value="{{ $st }}" {{ $appt->status == $st ? 'selected' : '' }}>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Panel lateral: Vencimientos + Barbero del mes + Servicios --}}
            <div class="sidebar-widgets">
                {{-- Vencimientos --}}
                <div class="widget-section card" id="widget-expiring">
                    <div class="card-title">Vencimientos en 7 días</div>
                    @if($expiringSoon->isEmpty())
                        <p style="color:var(--muted);font-size:0.8rem;">Sin vencimientos próximos.</p>
                    @else
                        @foreach($expiringSoon as $cm)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid var(--border);">
                            <div>
                                <div style="font-size:0.825rem;font-weight:500;">{{ $cm->client->name }}</div>
                                <div style="font-size:0.7rem;color:var(--muted);">{{ $cm->membership->name }}</div>
                            </div>
                            <div style="font-size:0.75rem;color:var(--yellow);font-weight:700;">{{ $cm->end_date->format('d/m') }}</div>
                        </div>
                        @endforeach
                    @endif
                </div>

                {{-- Barbero del mes --}}
                @if($topBarber)
                <div class="widget-section card" id="widget-top-barber" style="background:rgba(124,106,255,0.06); border-color: rgba(124,106,255,0.15);">
                    <div class="card-title">Barbero del mes</div>
                    <div style="font-size:1.15rem;font-weight:700;color:white;">{{ $topBarber->name }}</div>
                    <div style="font-size:0.8rem;color:var(--muted);margin-top:0.25rem;">{{ $topBarber->month_appointments }} turnos este mes</div>
                </div>
                @endif

                {{-- Servicios populares --}}
                <div class="widget-section card" id="widget-top-services">
                    <div class="card-title">Servicios populares</div>
                    @foreach($topServices as $svc)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:0.4rem 0;">
                        <span style="font-size:0.825rem;">{{ $svc->name }}</span>
                        <span style="font-size:0.75rem;font-weight:700;color:var(--accent2);">{{ $svc->month_count }} este mes</span>
                    </div>
                    @endforeach
                    @if($topServices->isEmpty())
                        <p style="color:var(--muted);font-size:0.8rem;">Sin datos este mes.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Widget: Accesos Rápidos al Catálogo ── --}}
    <div class="widget-section" id="widget-quick-access">
        <div style="font-size:0.65rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.65rem;">Accesos rápidos</div>
        <div class="quick-access-grid">
            <a href="{{ route('admin.barbers.index') }}" class="quick-card">
                <div class="quick-card-icon" style="background:rgba(124,106,255,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/></svg>
                </div>
                <div class="quick-card-count">{{ $barbers->count() }}</div>
                <div class="quick-card-label">Barberos</div>
            </a>
            <a href="{{ route('admin.services.index') }}" class="quick-card">
                <div class="quick-card-icon" style="background:rgba(34,197,94,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div class="quick-card-count">{{ $services->count() }}</div>
                <div class="quick-card-label">Servicios</div>
            </a>
            <a href="{{ route('admin.memberships.index') }}" class="quick-card">
                <div class="quick-card-icon" style="background:rgba(251,191,36,0.12);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div class="quick-card-count">{{ $memberships->count() }}</div>
                <div class="quick-card-label">Planes</div>
            </a>
        </div>
    </div>

</div>

{{-- Modal placeholder para nuevo turno --}}
<div class="modal-overlay" id="modal-new-appointment">
    <div class="modal">
        <div class="modal-title">Nuevo Turno</div>
        <p style="color:var(--muted);font-size:0.85rem;margin-bottom:1rem;">Para crear un turno, usá el sistema de reservas o gestionalo desde la sección de clientes.</p>
        <div style="display:flex;justify-content:flex-end;">
            <button class="btn btn-ghost" onclick="closeModal('modal-new-appointment')">Cerrar</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Widget Customizer ──
const WIDGETS = [
    'widget-stats-day',
    'widget-stats-month',
    'widget-agenda',
    'widget-expiring',
    'widget-top-barber',
    'widget-top-services',
    'widget-quick-access'
];

const TOGGLE_MAP = {
    'widget-stats-day'     : 'toggle-stats-day',
    'widget-stats-month'   : 'toggle-stats-month',
    'widget-agenda'        : 'toggle-agenda',
    'widget-expiring'      : 'toggle-expiring',
    'widget-top-barber'    : 'toggle-top-barber',
    'widget-top-services'  : 'toggle-top-services',
    'widget-quick-access'  : 'toggle-quick-access',
};

function loadWidgetPrefs() {
    const prefs = JSON.parse(localStorage.getItem('barberpro_widgets') || '{}');
    WIDGETS.forEach(id => {
        const isVisible = prefs[id] !== false; // default visible
        const el = document.getElementById(id);
        const toggleEl = document.getElementById(TOGGLE_MAP[id]);
        if (el) el.classList.toggle('hidden', !isVisible);
        if (toggleEl) toggleEl.checked = isVisible;
    });
}

function toggleWidget(widgetId, visible) {
    const el = document.getElementById(widgetId);
    if (el) el.classList.toggle('hidden', !visible);
    const prefs = JSON.parse(localStorage.getItem('barberpro_widgets') || '{}');
    prefs[widgetId] = visible;
    localStorage.setItem('barberpro_widgets', JSON.stringify(prefs));
}

function openCustomizer() {
    document.getElementById('customizer-overlay').classList.add('open');
    document.getElementById('customizer-panel').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeCustomizer() {
    document.getElementById('customizer-overlay').classList.remove('open');
    document.getElementById('customizer-panel').classList.remove('open');
    document.body.style.overflow = '';
}

// Init
document.addEventListener('DOMContentLoaded', loadWidgetPrefs);
</script>
@endpush
