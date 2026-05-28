@extends('admin.layout')

@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h3 style="margin:0;font-size:1.1rem;font-weight:700;">Gestión de Clientes</h3>
        <p style="margin:0.25rem 0 0;font-size:0.8rem;color:var(--muted);">{{ $clients->total() }} clientes registrados</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modal-new-client')">+ Nuevo cliente</button>
</div>

{{-- Filtros --}}
<div class="card" style="margin-bottom:1.25rem;">
    <form method="GET" action="{{ route('admin.clients.index') }}" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;">
        <div style="flex:1;min-width:200px;">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-control" placeholder="Nombre, teléfono o email..."
                   value="{{ request('search') }}">
        </div>
        <div style="min-width:140px;">
            <label class="form-label">Estado</label>
            <select name="status" class="form-control">
                <option value="">Todos</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Activos</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
            </select>
        </div>
        <button type="submit" class="btn btn-ghost">Filtrar</button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.clients.index') }}" class="btn btn-ghost">✕ Limpiar</a>
        @endif
    </form>
</div>

{{-- Tabla --}}
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Membresía activa</th>
                <th>Turnos</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
            <tr>
                <td>
                    <a href="{{ route('admin.clients.show', $client) }}"
                       style="font-weight:600;color:var(--text);text-decoration:none;">
                        {{ $client->name }}
                    </a>
                    @if($client->birthdate)
                        <div style="font-size:0.7rem;color:var(--muted);">
                            🎂 {{ $client->birthdate->format('d/m/Y') }}
                        </div>
                    @endif
                </td>
                <td style="color:var(--muted);">{{ $client->phone ?? '—' }}</td>
                <td style="color:var(--muted);">{{ $client->email ?? '—' }}</td>
                <td>
                    @if($client->activeMembership)
                        <span class="badge-status badge-confirmada">
                            {{ $client->activeMembership->membership->name ?? '—' }}
                        </span>
                        <div style="font-size:0.7rem;color:var(--muted);margin-top:2px;">
                            Vence: {{ $client->activeMembership->end_date->format('d/m/Y') }}
                        </div>
                    @else
                        <span style="color:var(--muted);font-size:0.8rem;">Sin membresía</span>
                    @endif
                </td>
                <td style="font-weight:600;">{{ $client->appointments_count }}</td>
                <td>
                    <span class="badge-status" style="{{ $client->is_active ? 'background:rgba(34,197,94,0.1);color:#22c55e' : 'background:rgba(239,68,68,0.1);color:#ef4444' }}">
                        {{ $client->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td>
                    <div style="display:flex;gap:0.4rem;">
                        <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-ghost btn-sm">Ver</a>
                        <form method="POST" action="{{ route('admin.clients.destroy', $client) }}"
                              onsubmit="return confirm('¿Eliminar cliente {{ $client->name }}?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">✕</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;padding:2rem;color:var(--muted);">
                    No se encontraron clientes.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginación --}}
    @if($clients->hasPages())
    <div style="display:flex;justify-content:center;gap:0.5rem;padding:1rem 0 0;">
        {{ $clients->links() }}
    </div>
    @endif
</div>

{{-- Modal: Nuevo Cliente --}}
<div class="modal-overlay" id="modal-new-client">
    <div class="modal">
        <div class="modal-title">👤 Nuevo Cliente</div>
        <form method="POST" action="{{ route('admin.clients.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre completo *</label>
                <input type="text" name="name" class="form-control" required autofocus>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de nacimiento</label>
                <input type="date" name="birthdate" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Observaciones</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Preferencias, alergias, etc."></textarea>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-new-client')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Cliente</button>
            </div>
        </form>
    </div>
</div>

@endsection
