@extends('admin.layout')

@section('title', 'Finanzas')
@section('page-title', 'Finanzas')

@section('content')

<style>
    .finance-table-desktop { display: block; }
    .finance-cards-mobile  { display: none; }

    @media (max-width: 768px) {
        .finance-table-desktop { display: none !important; }
        .finance-cards-mobile  { display: block !important; }

        .finance-period-form {
            flex-direction: column !important;
        }
        .finance-period-form > * {
            width: 100% !important;
        }
        .finance-custom-dates {
            flex-direction: column !important;
        }
        .finance-main-cols {
            grid-template-columns: 1fr !important;
        }
        .finance-stats-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .pending-card {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 0.9rem 1rem;
        margin-bottom: 0.65rem;
    }
    .pending-card:last-child { margin-bottom: 0; }
    .pending-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .pending-card-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.3rem 0.75rem;
        font-size: 0.8rem;
        color: var(--text2);
    }
    .pending-card-body span {
        font-size: 0.67rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: block;
    }
</style>

{{-- Selector de período --}}
<div class="card" style="margin-bottom:1.5rem;">
    <form method="GET" action="{{ route('admin.finance.index') }}" class="finance-period-form" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;">
        <div>
            <label class="form-label">Período</label>
            <select name="period" class="form-control" onchange="toggleCustom(this.value)">
                <option value="day"    {{ $period==='day'    ? 'selected':'' }}>Hoy</option>
                <option value="week"   {{ $period==='week'   ? 'selected':'' }}>Esta semana</option>
                <option value="month"  {{ $period==='month'  ? 'selected':'' }}>Este mes</option>
                <option value="year"   {{ $period==='year'   ? 'selected':'' }}>Este año</option>
                <option value="custom" {{ $period==='custom' ? 'selected':'' }}>Personalizado</option>
            </select>
        </div>
        <div id="custom-dates" class="finance-custom-dates" style="{{ $period==='custom' ? '' : 'display:none;' }} display:flex;gap:0.75rem;flex-wrap:wrap;">
            <div>
                <label class="form-label">Desde</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div>
                <label class="form-label">Hasta</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
        </div>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary">Aplicar</button>
            <a href="{{ route('admin.finance.payments') }}" class="btn btn-ghost">Ver todos los pagos →</a>
        </div>
    </form>
</div>

{{-- ── Métricas principales ────────────────────────────────────────────── --}}
<div class="grid finance-stats-grid gap-4" style="grid-template-columns:repeat(3,1fr); margin-bottom:1.5rem;">

    <div class="stat-card" style="background:linear-gradient(135deg,rgba(34,197,94,0.1),rgba(34,197,94,0.03));">
        <div class="icon-wrap" style="background:rgba(34,197,94,0.15);">💰</div>
        <div class="label">Total cobrado</div>
        <div class="value" style="color:var(--green);">${{ number_format($totalRevenue, 0, ',', '.') }}</div>
        <div class="sub">
            {{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}
        </div>
    </div>

    <div class="stat-card">
        <div class="icon-wrap" style="background:rgba(124,106,255,0.15);">🧾</div>
        <div class="label">Transacciones</div>
        <div class="value">{{ $byMethod->sum('count') }}</div>
        <div class="sub">Pagos registrados</div>
    </div>

    <div class="stat-card" style="background:rgba(239,68,68,0.05);border-color:rgba(239,68,68,0.2);">
        <div class="icon-wrap" style="background:rgba(239,68,68,0.15);">⏳</div>
        <div class="label">Pendiente de cobro</div>
        <div class="value" style="color:var(--red);">${{ number_format($pendingTotal, 0, ',', '.') }}</div>
        <div class="sub">{{ $pendingAppointments->count() }} turnos sin pagar</div>
    </div>
</div>

{{-- ── Fila: Por método + Por tipo ────────────────────────────────────── --}}
<div class="grid finance-main-cols gap-6" style="grid-template-columns:repeat(2,1fr); margin-bottom:1.5rem;">

    {{-- Por método de pago --}}
    <div class="card">
        <div class="card-title">💳 Ingresos por método de pago</div>
        @forelse($byMethod as $row)
        @php $pct = $totalRevenue > 0 ? ($row->total / $totalRevenue) * 100 : 0; @endphp
        <div style="margin-bottom:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.3rem;">
                <div style="font-size:0.85rem;font-weight:500;text-transform:capitalize;">
                    @if($row->method==='efectivo') 💵 @elseif($row->method==='transferencia') 🏦 @elseif($row->method==='tarjeta') 💳 @else 🔄 @endif
                    {{ ucfirst($row->method) }}
                </div>
                <div style="font-size:0.85rem;font-weight:700;color:var(--green);">
                    ${{ number_format($row->total, 0, ',', '.') }}
                    <span style="color:var(--muted);font-weight:400;font-size:0.75rem;">({{ $row->count }})</span>
                </div>
            </div>
            <div style="height:6px;background:var(--surface2);border-radius:999px;overflow:hidden;">
                <div style="height:100%;width:{{ $pct }}%;background:var(--accent);border-radius:999px;transition:width 0.5s;"></div>
            </div>
            <div style="font-size:0.7rem;color:var(--muted);margin-top:0.2rem;">{{ number_format($pct, 1) }}% del total</div>
        </div>
        @empty
        <p style="color:var(--muted);font-size:0.85rem;">Sin registros en este período.</p>
        @endforelse
    </div>

    {{-- Por tipo --}}
    <div class="card">
        <div class="card-title">📂 Ingresos por tipo</div>
        @forelse($byType as $row)
        @php $pct = $totalRevenue > 0 ? ($row->total / $totalRevenue) * 100 : 0; @endphp
        <div style="margin-bottom:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.3rem;">
                <div style="font-size:0.85rem;font-weight:500;">
                    {{ $row->type === 'cita' ? '📅 Citas' : '💳 Membresías' }}
                </div>
                <div style="font-size:0.85rem;font-weight:700;color:var(--accent2);">
                    ${{ number_format($row->total, 0, ',', '.') }}
                    <span style="color:var(--muted);font-weight:400;font-size:0.75rem;">({{ $row->count }})</span>
                </div>
            </div>
            <div style="height:6px;background:var(--surface2);border-radius:999px;overflow:hidden;">
                <div style="height:100%;width:{{ $pct }}%;background:var(--accent2);border-radius:999px;"></div>
            </div>
            <div style="font-size:0.7rem;color:var(--muted);margin-top:0.2rem;">{{ number_format($pct, 1) }}% del total</div>
        </div>
        @empty
        <p style="color:var(--muted);font-size:0.85rem;">Sin registros en este período.</p>
        @endforelse

        {{-- Ingresos diarios --}}
        @if($dailyRevenue->count() > 1)
        <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border);">
            <div style="font-size:0.75rem;font-weight:600;color:var(--muted);margin-bottom:0.6rem;text-transform:uppercase;letter-spacing:0.05em;">Evolución diaria</div>
            @php $maxDay = $dailyRevenue->max('total'); @endphp
            @foreach($dailyRevenue as $day)
            @php $dpct = $maxDay > 0 ? ($day->total / $maxDay) * 100 : 0; @endphp
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.3rem;">
                <span style="font-size:0.7rem;color:var(--muted);width:40px;flex-shrink:0;">
                    {{ \Carbon\Carbon::parse($day->day)->format('d/m') }}
                </span>
                <div style="flex:1;height:5px;background:var(--surface2);border-radius:999px;">
                    <div style="height:100%;width:{{ $dpct }}%;background:var(--green);border-radius:999px;"></div>
                </div>
                <span style="font-size:0.7rem;color:var(--green);width:60px;text-align:right;">
                    ${{ number_format($day->total, 0, ',', '.') }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ── Pendientes de cobro ─────────────────────────────────────────────── --}}
@if($pendingAppointments->count() > 0)

{{-- Desktop table --}}
<div class="card finance-table-desktop">
    <div class="card-title">⏳ Turnos pendientes de cobro</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Barbero</th>
                    <th>Servicio</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingAppointments as $appt)
                <tr>
                    <td>{{ $appt->appointment_date->format('d/m/Y') }}</td>
                    <td>
                        @if($appt->client)
                            <a href="{{ route('admin.clients.show', $appt->client) }}" style="color:var(--text);font-weight:500;">
                                {{ $appt->client->name }}
                            </a>
                        @else
                            {{ $appt->customer_name }}
                        @endif
                    </td>
                    <td>{{ $appt->barber->name ?? '—' }}</td>
                    <td>{{ $appt->service->name ?? '—' }}</td>
                    <td style="font-weight:700;color:var(--yellow);">${{ number_format($appt->total_price, 0, ',', '.') }}</td>
                    <td><span class="badge-status badge-{{ strtolower($appt->status) }}">{{ $appt->status }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('admin.finance.pay', $appt) }}" style="display:inline-flex; gap: 0.4rem; align-items: center;">
                            @csrf
                            <select name="payment_method" class="form-control" style="width: auto; padding: 0.2rem 0.5rem; font-size: 0.75rem;" required>
                                <option value="" disabled selected>Método...</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transf.</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="padding: 0.2rem 0.6rem; font-size: 0.75rem; border-radius: 4px;" onclick="return confirm('¿Confirmar cobro de ${{ number_format($appt->total_price, 0, ',', '.') }}?')">Cobrar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Mobile cards --}}
<div class="finance-cards-mobile">
    <div style="font-size:0.75rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:0.75rem;padding:0 0.25rem;">
        ⏳ Turnos pendientes de cobro
    </div>
    @foreach($pendingAppointments as $appt)
    <div class="pending-card">
        <div class="pending-card-header">
            <div style="font-size:0.85rem;font-weight:600;">
                @if($appt->client)
                    <a href="{{ route('admin.clients.show', $appt->client) }}" style="color:var(--text);text-decoration:none;">{{ $appt->client->name }}</a>
                @else
                    {{ $appt->customer_name }}
                @endif
            </div>
            <div style="font-size:1rem;font-weight:700;color:var(--yellow);">${{ number_format($appt->total_price, 0, ',', '.') }}</div>
        </div>
        <div class="pending-card-body">
            <div>
                <span>Fecha</span>
                {{ $appt->appointment_date->format('d/m/Y') }}
            </div>
            <div>
                <span>Barbero</span>
                {{ $appt->barber->name ?? '—' }}
            </div>
            <div>
                <span>Servicio</span>
                {{ $appt->service->name ?? '—' }}
            </div>
            <div>
                <span>Estado</span>
                <span class="badge-status badge-{{ strtolower($appt->status) }}" style="margin-top:3px;">{{ $appt->status }}</span>
            </div>
            <div style="grid-column: span 2; margin-top: 0.5rem; border-top: 1px solid var(--border); padding-top: 0.75rem;">
                <form method="POST" action="{{ route('admin.finance.pay', $appt) }}" style="display:flex; gap: 0.5rem; align-items: center; width: 100%;">
                    @csrf
                    <select name="payment_method" class="form-control" style="flex: 1; padding: 0.3rem 0.5rem; font-size: 0.8rem;" required>
                        <option value="" disabled selected>Seleccionar método...</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                    <button type="submit" class="btn btn-primary" style="padding: 0.3rem 0.75rem; font-size: 0.8rem; border-radius: 4px;" onclick="return confirm('¿Confirmar cobro de ${{ number_format($appt->total_price, 0, ',', '.') }}?')">Cobrar</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endif

@endsection

@push('scripts')
<script>
function toggleCustom(val) {
    document.getElementById('custom-dates').style.display = val === 'custom' ? 'flex' : 'none';
}
</script>
@endpush
