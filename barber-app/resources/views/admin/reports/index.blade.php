@extends('admin.layout')

@section('title', 'Reportes')
@section('page-title', 'Reportes y Estadísticas')

@section('content')

<style>
    .reports-filter-form {
        display: flex;
        gap: 1rem;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .reports-filter-form > div {
        flex: 1;
        min-width: 140px;
    }
    @media (max-width: 480px) {
        .reports-filter-form { flex-direction: column; }
        .reports-filter-form > div { width: 100%; }
        .reports-filter-form .btn { width: 100%; justify-content: center; }
    }
</style>

{{-- Filtro de período --}}
<div class="card" style="margin-bottom:1.5rem;">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="reports-filter-form">
        <div>
            <label class="form-label">Desde</label>
            <input type="date" name="from" class="form-control" value="{{ $from->format('Y-m-d') }}">
        </div>
        <div>
            <label class="form-label">Hasta</label>
            <input type="date" name="to" class="form-control" value="{{ $to->format('Y-m-d') }}">
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <span style="align-self:center;color:var(--muted);font-size:0.8rem;white-space:nowrap;">
            {{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}
        </span>
    </form>
</div>

{{-- ── Fila 1: Servicios + Barberos ────────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-6" style="margin-bottom:1.5rem;">

    {{-- Servicios más vendidos --}}
    <div class="card">
        <div class="card-title">🔥 Servicios más vendidos</div>
        @forelse($topServices as $i => $svc)
        @php $max = $topServices->first()->total ?? 1; $pct = ($svc->total / $max) * 100; @endphp
        <div style="margin-bottom:0.85rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.25rem;">
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <span style="width:20px;height:20px;border-radius:50%;background:rgba(124,106,255,0.2);
                        color:var(--accent2);font-size:0.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;">
                        {{ $i+1 }}
                    </span>
                    <span style="font-size:0.85rem;font-weight:500;">{{ $svc->service->name ?? 'N/A' }}</span>
                </div>
                <span style="font-size:0.85rem;font-weight:700;color:var(--accent2);">{{ $svc->total }} turnos</span>
            </div>
            <div style="height:5px;background:var(--surface2);border-radius:999px;">
                <div style="height:100%;width:{{ $pct }}%;background:var(--accent);border-radius:999px;"></div>
            </div>
        </div>
        @empty
        <p style="color:var(--muted);font-size:0.85rem;">Sin datos en este período.</p>
        @endforelse
    </div>

    {{-- Rendimiento de barberos --}}
    <div class="card">
        <div class="card-title">✂️ Rendimiento por barbero</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Barbero</th>
                    <th>Turnos</th>
                    <th>Ingresos</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barberStats as $b)
                <tr>
                    <td style="font-weight:500;">{{ $b->name }}</td>
                    <td style="font-weight:700;color:var(--accent2);">{{ $b->total_appointments }}</td>
                    <td style="font-weight:700;color:var(--green);">
                        ${{ number_format($b->total_revenue ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="color:var(--muted);text-align:center;">Sin datos.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Fila 2: Membresías + Vencimientos ────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-6" style="margin-bottom:1.5rem;">

    {{-- Estado de membresías --}}
    <div class="card">
        <div class="card-title">💳 Membresías activas por plan</div>
        @forelse($membershipStats as $m)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0;border-bottom:1px solid var(--border);">
            <div>
                <div style="font-size:0.9rem;font-weight:600;">{{ $m->name }}</div>
                <div style="font-size:0.75rem;color:var(--muted);">${{ number_format($m->price, 0, ',', '.') }}/mes · {{ $m->visits }} visitas</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:1.5rem;font-weight:800;color:var(--accent2);">{{ $m->active_count }}</div>
                <div style="font-size:0.7rem;color:var(--muted);">clientes activos</div>
            </div>
        </div>
        @empty
        <p style="color:var(--muted);font-size:0.85rem;">Sin planes configurados.</p>
        @endforelse
    </div>

    {{-- Vencimientos próximos --}}
    <div class="card">
        <div class="card-title">⚠️ Próximos a vencer (7 días)</div>
        @forelse($expiringSoon as $cm)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0;border-bottom:1px solid var(--border);">
            <div>
                <a href="{{ route('admin.clients.show', $cm->client) }}" style="font-size:0.875rem;font-weight:600;color:var(--text);text-decoration:none;">
                    {{ $cm->client->name }}
                </a>
                <div style="font-size:0.75rem;color:var(--muted);">{{ $cm->membership->name ?? '—' }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:0.85rem;font-weight:700;color:var(--yellow);">{{ $cm->end_date->format('d/m/Y') }}</div>
                <div style="font-size:0.7rem;color:var(--muted);">{{ $cm->end_date->diffForHumans() }}</div>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:1.5rem;color:var(--muted);">
            <div style="font-size:1.5rem;">✅</div>
            Sin vencimientos próximos
        </div>
        @endforelse
    </div>
</div>

{{-- ── Fila 3: Clientes frecuentes + Inactivos ─────────────────────────── --}}
<div class="grid grid-cols-2 gap-6">

    {{-- Clientes frecuentes --}}
    <div class="card">
        <div class="card-title">🏆 Clientes más frecuentes</div>
        <table class="table">
            <thead>
                <tr><th>#</th><th>Cliente</th><th>Visitas</th></tr>
            </thead>
            <tbody>
                @forelse($topClients as $i => $c)
                <tr>
                    <td style="color:var(--muted);width:30px;">{{ $i+1 }}</td>
                    <td>
                        <a href="{{ route('admin.clients.show', $c) }}" style="color:var(--text);font-weight:500;text-decoration:none;">
                            {{ $c->name }}
                        </a>
                    </td>
                    <td>
                        <span style="font-weight:700;color:var(--accent2);">{{ $c->total_visits }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="color:var(--muted);text-align:center;">Sin datos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Clientes inactivos --}}
    <div class="card">
        <div class="card-title">😴 Clientes inactivos (+60 días sin visita)</div>
        @if($inactiveClients->isEmpty())
        <div style="text-align:center;padding:1.5rem;color:var(--muted);">
            <div style="font-size:1.5rem;">🎉</div>
            ¡Todos los clientes han visitado recientemente!
        </div>
        @else
        <table class="table">
            <thead>
                <tr><th>Cliente</th><th>Teléfono</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach($inactiveClients as $c)
                <tr>
                    <td style="font-weight:500;">{{ $c->name }}</td>
                    <td style="color:var(--muted);">{{ $c->phone ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.clients.show', $c) }}" class="btn btn-ghost btn-sm">Ver</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

@endsection
