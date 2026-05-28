@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- ── Métricas del día ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-4 gap-4" style="margin-bottom:1.5rem;">

    <div class="stat-card">
        <div class="icon-wrap" style="background:rgba(124,106,255,0.15);">📅</div>
        <div class="label">Turnos hoy</div>
        <div class="value">{{ $todayCount }}</div>
        <div class="sub">{{ now()->format('d/m/Y') }}</div>
    </div>

    <div class="stat-card">
        <div class="icon-wrap" style="background:rgba(34,197,94,0.15);">💵</div>
        <div class="label">Ganancia del día</div>
        <div class="value">${{ number_format($todayRevenue, 0, ',', '.') }}</div>
        <div class="sub">Pagos cobrados</div>
    </div>

    <div class="stat-card">
        <div class="icon-wrap" style="background:rgba(59,130,246,0.15);">📈</div>
        <div class="label">Ganancia del mes</div>
        <div class="value">${{ number_format($monthRevenue, 0, ',', '.') }}</div>
        <div class="sub">{{ now()->translatedFormat('F Y') }}</div>
    </div>

    <div class="stat-card">
        <div class="icon-wrap" style="background:rgba(234,179,8,0.15);">💳</div>
        <div class="label">Membresías activas</div>
        <div class="value">{{ $activeMemberships }}</div>
        <div class="sub">{{ $newClientsMonth }} clientes nuevos este mes</div>
    </div>

</div>

{{-- ── Fila principal: Turnos de hoy + Métricas ──────────────────────────── --}}
<div class="grid gap-6" style="grid-template-columns:1fr 340px; margin-bottom:1.5rem;">

    {{-- Turnos de hoy --}}
    <div class="card" id="turnos">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <div class="card-title" style="margin:0;">📅 Turnos de hoy</div>
            <button class="btn btn-primary btn-sm" onclick="openModal('modal-new-appointment')">+ Nuevo</button>
        </div>

        @if($todayAppointments->isEmpty())
            <div style="text-align:center;padding:2rem;color:var(--muted);">
                <div style="font-size:2rem;margin-bottom:0.5rem;">📭</div>
                No hay turnos programados para hoy
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Cliente</th>
                        <th>Barbero</th>
                        <th>Servicio</th>
                        <th>Pago</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayAppointments as $appt)
                    <tr>
                        <td style="font-weight:600;color:var(--accent2);">
                            {{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}
                        </td>
                        <td>
                            @if($appt->client)
                                <a href="{{ route('admin.clients.show', $appt->client) }}"
                                   style="color:var(--text);text-decoration:none;font-weight:500;">
                                    {{ $appt->client->name }}
                                </a>
                            @else
                                <span style="color:var(--muted);">{{ $appt->customer_name }}</span>
                            @endif
                        </td>
                        <td>{{ $appt->barber->name ?? '—' }}</td>
                        <td>{{ $appt->service->name ?? '—' }}</td>
                        <td>
                            <span class="badge-status {{ $appt->payment_status === 'pagado' ? 'badge-pagado' : 'badge-pendiente-pay' }}">
                                ${{ number_format($appt->total_price, 0, ',', '.') }}
                                · {{ $appt->payment_method }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-status badge-{{ strtolower($appt->status) }}">
                                {{ $appt->status }}
                            </span>
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
                </tbody>
            </table>
        @endif
    </div>

    {{-- Panel lateral derecho --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Vencimientos próximos --}}
        <div class="card">
            <div class="card-title">⚠️ Vencimientos en 7 días</div>
            @if($expiringSoon->isEmpty())
                <p style="color:var(--muted);font-size:0.8rem;">Sin vencimientos próximos.</p>
            @else
                @foreach($expiringSoon as $cm)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid var(--border);">
                    <div>
                        <div style="font-size:0.825rem;font-weight:500;">{{ $cm->client->name }}</div>
                        <div style="font-size:0.7rem;color:var(--muted);">{{ $cm->membership->name }}</div>
                    </div>
                    <div style="font-size:0.75rem;color:var(--yellow);font-weight:600;">
                        {{ $cm->end_date->format('d/m') }}
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        {{-- Top barbero del mes --}}
        @if($topBarber)
        <div class="card" style="background:linear-gradient(135deg,rgba(124,106,255,0.15),rgba(124,106,255,0.05));">
            <div class="card-title">🏆 Barbero del mes</div>
            <div style="font-size:1.1rem;font-weight:700;color:white;">{{ $topBarber->name }}</div>
            <div style="font-size:0.8rem;color:var(--muted);">{{ $topBarber->month_appointments }} turnos este mes</div>
        </div>
        @endif

        {{-- Servicios más vendidos --}}
        <div class="card">
            <div class="card-title">🔥 Servicios populares</div>
            @foreach($topServices as $svc)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.4rem 0;">
                <span style="font-size:0.825rem;">{{ $svc->name }}</span>
                <span style="font-size:0.75rem;font-weight:600;color:var(--accent2);">{{ $svc->month_count }} este mes</span>
            </div>
            @endforeach
        </div>

    </div>
</div>

{{-- ── Gestión: Barberos, Servicios, Membresías ─────────────────────────── --}}
<div class="grid grid-cols-3 gap-6" style="margin-bottom:1.5rem;">

    {{-- Barberos --}}
    <div class="card" id="barberos">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <div class="card-title" style="margin:0;">✂️ Barberos</div>
            <button class="btn btn-ghost btn-sm" onclick="openModal('modal-barber')">+ Agregar</button>
        </div>
        @foreach($barbers as $b)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid rgba(46,50,72,0.5);">
            <div>
                <div style="font-size:0.825rem;font-weight:500;">{{ $b->name }}</div>
                @if($b->specialties)
                    <div style="font-size:0.7rem;color:var(--muted);">{{ implode(', ', $b->specialties) }}</div>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <span class="badge-status" style="{{ $b->is_active ? 'background:rgba(34,197,94,0.1);color:#22c55e' : 'background:rgba(239,68,68,0.1);color:#ef4444' }}">
                    {{ $b->is_active ? 'Activo' : 'Inactivo' }}
                </span>
                <form method="POST" action="{{ route('admin.barber.destroy', $b) }}" onsubmit="return confirm('¿Eliminar?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">✕</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Servicios --}}
    <div class="card" id="servicios">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <div class="card-title" style="margin:0;">📋 Servicios</div>
            <button class="btn btn-ghost btn-sm" onclick="openModal('modal-service')">+ Agregar</button>
        </div>
        @foreach($services as $s)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid rgba(46,50,72,0.5);">
            <div>
                <div style="font-size:0.825rem;font-weight:500;">{{ $s->name }}</div>
                <div style="font-size:0.7rem;color:var(--muted);">{{ $s->duration_min }} min</div>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <span style="font-size:0.825rem;font-weight:600;color:var(--green);">${{ number_format($s->price, 0, ',', '.') }}</span>
                <form method="POST" action="{{ route('admin.service.destroy', $s) }}" onsubmit="return confirm('¿Eliminar?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">✕</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Membresías --}}
    <div class="card" id="memberships">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <div class="card-title" style="margin:0;">💳 Planes</div>
            <button class="btn btn-ghost btn-sm" onclick="openModal('modal-membership')">+ Agregar</button>
        </div>
        @foreach($memberships as $m)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid rgba(46,50,72,0.5);">
            <div>
                <div style="font-size:0.825rem;font-weight:500;">{{ $m->name }}</div>
                <div style="font-size:0.7rem;color:var(--muted);">{{ $m->visits }} visitas</div>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <span style="font-size:0.825rem;font-weight:600;color:var(--accent2);">${{ number_format($m->price, 0, ',', '.') }}</span>
                <form method="POST" action="{{ route('admin.membership.destroy', $m) }}" onsubmit="return confirm('¿Eliminar?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">✕</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── MODALES ───────────────────────────────────────────────────────────── --}}

{{-- Modal: Nuevo Barbero --}}
<div class="modal-overlay" id="modal-barber">
    <div class="modal">
        <div class="modal-title">✂️ Agregar Barbero</div>
        <form method="POST" action="{{ route('admin.barber.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Especialidades (separadas por coma)</label>
                <input type="text" name="specialties" class="form-control" placeholder="Fade, Corte clásico, Barba">
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_active" id="barber-active" checked style="width:16px;height:16px;">
                <label for="barber-active" class="form-label" style="margin:0;">Activo</label>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-barber')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Nuevo Servicio --}}
<div class="modal-overlay" id="modal-service">
    <div class="modal">
        <div class="modal-title">📋 Agregar Servicio</div>
        <form method="POST" action="{{ route('admin.service.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select name="category" class="form-control" required>
                        <option value="corte">Corte</option>
                        <option value="barba">Barba</option>
                        <option value="combo">Combo</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Duración (min) *</label>
                    <input type="number" name="duration_min" class="form-control" value="30" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Precio *</label>
                <input type="number" name="price" step="0.01" class="form-control" required>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-service')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Nuevo Plan de Membresía --}}
<div class="modal-overlay" id="modal-membership">
    <div class="modal">
        <div class="modal-title">💳 Nuevo Plan de Membresía</div>
        <form method="POST" action="{{ route('admin.membership.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre del plan *</label>
                <input type="text" name="name" class="form-control" placeholder="Básica, Media, Pro..." required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Precio mensual *</label>
                    <input type="number" name="price" step="0.01" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Visitas incluidas *</label>
                    <input type="text" name="visits" class="form-control" placeholder="4, 8, ilimitadas" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Beneficios</label>
                <textarea name="benefits" class="form-control" rows="3" placeholder="Descripción del plan..."></textarea>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-membership')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection
