@extends('admin.layout')

@section('title', $client->name)
@section('page-title', 'Perfil de Cliente')

@section('content')

<div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
    <a href="{{ route('admin.clients.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
    <span style="color:var(--muted);">/</span>
    <span style="font-size:0.9rem;font-weight:600;">{{ $client->name }}</span>
</div>

<div class="grid gap-6" style="grid-template-columns:320px 1fr;">

    {{-- ── Columna izquierda: Datos del cliente ─────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Tarjeta principal --}}
        <div class="card" style="text-align:center;padding:1.75rem 1.25rem;">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));
                display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 1rem;">
                {{ mb_strtoupper(mb_substr($client->name, 0, 1)) }}
            </div>
            <h2 style="margin:0;font-size:1.1rem;font-weight:700;">{{ $client->name }}</h2>
            <p style="margin:0.25rem 0 1rem;font-size:0.8rem;color:var(--muted);">
                Cliente desde {{ $client->created_at->format('d/m/Y') }}
            </p>
            <span class="badge-status" style="{{ $client->is_active ? 'background:rgba(34,197,94,0.1);color:#22c55e' : 'background:rgba(239,68,68,0.1);color:#ef4444' }}">
                {{ $client->is_active ? '✓ Activo' : '✗ Inactivo' }}
            </span>
        </div>

        {{-- Info de contacto --}}
        <div class="card">
            <div class="card-title">Información de contacto</div>
            @if($client->phone)
            <div style="display:flex;gap:0.5rem;align-items:center;padding:0.4rem 0;font-size:0.85rem;">
                <span>📱</span> {{ $client->phone }}
            </div>
            @endif
            @if($client->email)
            <div style="display:flex;gap:0.5rem;align-items:center;padding:0.4rem 0;font-size:0.85rem;">
                <span>✉️</span> {{ $client->email }}
            </div>
            @endif
            @if($client->birthdate)
            <div style="display:flex;gap:0.5rem;align-items:center;padding:0.4rem 0;font-size:0.85rem;">
                <span>🎂</span> {{ $client->birthdate->format('d/m/Y') }}
            </div>
            @endif
            @if($client->notes)
            <div style="margin-top:0.75rem;padding:0.6rem;background:var(--surface2);border-radius:8px;font-size:0.8rem;color:var(--muted);">
                {{ $client->notes }}
            </div>
            @endif
            <button class="btn btn-ghost btn-sm" style="margin-top:1rem;width:100%;" onclick="openModal('modal-edit-client')">
                ✏️ Editar datos
            </button>
        </div>

        {{-- Estadísticas --}}
        <div class="card">
            <div class="card-title">Estadísticas</div>
            <div style="display:flex;flex-direction:column;gap:0.6rem;">
                <div style="display:flex;justify-content:space-between;font-size:0.85rem;">
                    <span style="color:var(--muted);">Total de visitas</span>
                    <span style="font-weight:700;">{{ $client->appointments->count() }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:0.85rem;">
                    <span style="color:var(--muted);">Total pagado</span>
                    <span style="font-weight:700;color:var(--green);">
                        ${{ number_format($client->payments->sum('amount'), 0, ',', '.') }}
                    </span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:0.85rem;">
                    <span style="color:var(--muted);">Última visita</span>
                    <span style="font-weight:600;">
                        {{ $client->appointments->sortByDesc('appointment_date')->first()?->appointment_date?->format('d/m/Y') ?? '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Membresía activa --}}
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                <div class="card-title" style="margin:0;">💳 Membresía</div>
                <button class="btn btn-primary btn-sm" onclick="openModal('modal-add-membership')">+ Asignar</button>
            </div>
            @php $activeMem = $client->clientMemberships->where('payment_status','pagado')->sortByDesc('end_date')->first(); @endphp
            @if($activeMem && !$activeMem->isExpired())
                <div style="padding:0.75rem;background:rgba(124,106,255,0.1);border-radius:8px;border:1px solid rgba(124,106,255,0.2);">
                    <div style="font-weight:700;font-size:0.9rem;color:var(--accent2);">{{ $activeMem->membership->name }}</div>
                    <div style="font-size:0.75rem;color:var(--muted);margin-top:0.25rem;">
                        Vence: {{ $activeMem->end_date->format('d/m/Y') }}
                        ({{ $activeMem->end_date->diffForHumans() }})
                    </div>
                    <div style="font-size:0.75rem;margin-top:0.4rem;">
                        <span style="color:var(--muted);">Servicios restantes:</span>
                        <strong style="color:white;">{{ $activeMem->services_remaining }}</strong>
                    </div>
                </div>
            @else
                <p style="color:var(--muted);font-size:0.825rem;">Sin membresía activa.</p>
            @endif

            {{-- Historial de membresías --}}
            @if($client->clientMemberships->count() > 0)
            <div style="margin-top:0.75rem;">
                <div style="font-size:0.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.4rem;">Historial</div>
                @foreach($client->clientMemberships->sortByDesc('start_date')->take(4) as $cm)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.35rem 0;border-bottom:1px solid rgba(46,50,72,0.4);">
                    <div>
                        <div style="font-size:0.78rem;font-weight:500;">{{ $cm->membership->name ?? '—' }}</div>
                        <div style="font-size:0.68rem;color:var(--muted);">
                            {{ $cm->start_date->format('d/m/Y') }} → {{ $cm->end_date->format('d/m/Y') }}
                        </div>
                    </div>
                    <span class="badge-status {{ $cm->payment_status === 'pagado' ? 'badge-pagado' : 'badge-pendiente-pay' }}">
                        {{ $cm->payment_status }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

    {{-- ── Columna derecha: Historial ───────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Historial de turnos --}}
        <div class="card">
            <div class="card-title">📅 Historial de turnos</div>
            @if($client->appointments->isEmpty())
                <p style="color:var(--muted);font-size:0.85rem;">Sin turnos registrados.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Barbero</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Método</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($client->appointments->sortByDesc('appointment_date') as $appt)
                        <tr>
                            <td style="font-weight:500;">{{ $appt->appointment_date->format('d/m/Y') }}</td>
                            <td style="color:var(--muted);">{{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}</td>
                            <td>{{ $appt->barber->name ?? '—' }}</td>
                            <td>{{ $appt->service->name ?? '—' }}</td>
                            <td>
                                <span class="badge-status badge-{{ strtolower($appt->status) }}">{{ $appt->status }}</span>
                            </td>
                            <td style="font-weight:600;color:var(--green);">${{ number_format($appt->total_price, 0, ',', '.') }}</td>
                            <td style="color:var(--muted);font-size:0.78rem;">{{ $appt->payment_method ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Historial de pagos --}}
        <div class="card">
            <div class="card-title">💳 Historial de pagos</div>
            @if($client->payments->isEmpty())
                <p style="color:var(--muted);font-size:0.85rem;">Sin pagos registrados.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Método</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($client->payments->sortByDesc('paid_at') as $pay)
                        <tr>
                            <td>{{ $pay->paid_at?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <span class="badge-status" style="background:rgba(124,106,255,0.1);color:var(--accent2);">
                                    {{ $pay->type }}
                                </span>
                            </td>
                            <td style="color:var(--muted);font-size:0.8rem;">{{ $pay->description ?? '—' }}</td>
                            <td style="color:var(--muted);">{{ $pay->method }}</td>
                            <td style="font-weight:700;color:var(--green);">${{ number_format($pay->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>
</div>

{{-- ── MODALES ──────────────────────────────────────────────────────────── --}}

{{-- Modal: Editar Cliente --}}
<div class="modal-overlay" id="modal-edit-client">
    <div class="modal">
        <div class="modal-title">✏️ Editar Cliente</div>
        <form method="POST" action="{{ route('admin.clients.update', $client) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-control" value="{{ $client->name }}" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" name="phone" class="form-control" value="{{ $client->phone }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $client->email }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de nacimiento</label>
                <input type="date" name="birthdate" class="form-control" value="{{ $client->birthdate?->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Observaciones</label>
                <textarea name="notes" class="form-control" rows="2">{{ $client->notes }}</textarea>
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_active" id="edit-active" {{ $client->is_active ? 'checked' : '' }} style="width:16px;height:16px;">
                <label for="edit-active" class="form-label" style="margin:0;">Cliente activo</label>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit-client')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Asignar Membresía --}}
<div class="modal-overlay" id="modal-add-membership">
    <div class="modal">
        <div class="modal-title">💳 Asignar Membresía</div>
        <form method="POST" action="{{ route('admin.clients.membership', $client) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Plan *</label>
                <select name="membership_id" class="form-control" required>
                    <option value="">— Seleccionar plan —</option>
                    @foreach($memberships as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} — ${{ number_format($m->price, 0, ',', '.') }} / {{ $m->visits }} visitas</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de inicio *</label>
                <input type="date" name="start_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Método de pago *</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado del pago *</label>
                    <select name="payment_status" class="form-control" required>
                        <option value="pagado">Pagado</option>
                        <option value="pendiente">Pendiente</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-add-membership')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Asignar</button>
            </div>
        </form>
    </div>
</div>

@endsection
