@extends('admin.layout')

@section('title', 'Servicios')
@section('page-title', 'Servicios')

@section('content')

<style>
    .services-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }
    .services-header h2 { font-size: 1.1rem; font-weight: 700; color: var(--text); }
    .services-header p { font-size: 0.78rem; color: var(--muted); margin-top: 2px; }

    .category-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .cat-tab {
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid var(--border2);
        background: var(--surface2);
        color: var(--text2);
        transition: all 0.15s;
    }
    .cat-tab:hover { border-color: var(--accent); color: var(--accent2); }
    .cat-tab.active { background: var(--accent-bg); color: var(--accent2); border-color: var(--accent); }

    .services-table-wrap { overflow-x: auto; }

    .service-category-group {
        margin-bottom: 1.5rem;
    }
    .service-category-label {
        font-size: 0.65rem;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border);
        margin-bottom: 0;
    }

    .cat-badge {
        display: inline-flex;
        padding: 0.18rem 0.55rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .cat-corte   { background: rgba(99,102,241,0.12); color: #818cf8; }
    .cat-barba   { background: rgba(34,197,94,0.12);  color: #4ade80; }
    .cat-combo   { background: rgba(251,191,36,0.12); color: #fbbf24; }
    .cat-otro    { background: rgba(100,116,139,0.12); color: #94a3b8; }

    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--muted);
    }
    .empty-state .empty-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
    .empty-state h3 { font-size: 0.95rem; font-weight: 600; color: var(--text2); margin-bottom: 0.4rem; }

    @media (max-width: 600px) {
        .services-header { flex-wrap: wrap; gap: 0.75rem; }
    }
</style>

{{-- Page header --}}
<div class="services-header">
    <div>
        <h2>📋 Servicios</h2>
        <p>{{ $services->count() }} servicio{{ $services->count() != 1 ? 's' : '' }} registrado{{ $services->count() != 1 ? 's' : '' }}</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modal-service-new')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Agregar servicio
    </button>
</div>

{{-- Services table --}}
@if($services->isEmpty())
    <div class="card empty-state">
        <div class="empty-icon">📋</div>
        <h3>Sin servicios todavía</h3>
        <p>Agregá tus servicios para que los clientes puedan reservar.</p>
        <button class="btn btn-primary" style="margin-top:1rem;" onclick="openModal('modal-service-new')">+ Agregar servicio</button>
    </div>
@else
    <div class="card" style="padding:0;overflow:hidden;">
        @php $grouped = $services->groupBy('category'); @endphp
        @foreach($grouped as $category => $categoryServices)
        <div class="service-category-group">
            <div class="service-category-label">
                @php
                    $catLabels = ['corte'=>'✂️ Corte','barba'=>'🧔 Barba','combo'=>'⚡ Combo','otro'=>'📌 Otro'];
                @endphp
                {{ $catLabels[$category] ?? ucfirst($category) }} · {{ $categoryServices->count() }} servicio{{ $categoryServices->count() != 1 ? 's' : '' }}
            </div>
            <div class="services-table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Duración</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th style="width:120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categoryServices as $s)
                        <tr>
                            <td style="font-weight:600;">{{ $s->name }}</td>
                            <td>
                                <span class="cat-badge cat-{{ strtolower($s->category) }}">{{ ucfirst($s->category) }}</span>
                            </td>
                            <td style="color:var(--muted);">{{ $s->duration_min }} min</td>
                            <td style="font-weight:700;color:var(--green);">${{ number_format($s->price, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge-status" style="{{ $s->is_active ? 'background:rgba(34,197,94,0.1);color:#22c55e;border:1px solid rgba(34,197,94,0.2)' : 'background:rgba(248,113,113,0.1);color:#f87171;border:1px solid rgba(248,113,113,0.2)' }}">
                                    {{ $s->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:0.35rem;justify-content:flex-end;">
                                    <button class="btn btn-ghost btn-sm" onclick="openModal('modal-service-edit-{{ $s->id }}')">✏️</button>
                                    <form method="POST" action="{{ route('admin.service.destroy', $s) }}" onsubmit="return confirm('¿Eliminar {{ $s->name }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">✕</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Edit Modals --}}
    @foreach($services as $s)
    <div class="modal-overlay" id="modal-service-edit-{{ $s->id }}">
        <div class="modal">
            <div class="modal-title">✏️ Editar — {{ $s->name }}</div>
            <form method="POST" action="{{ route('admin.service.update', $s) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" class="form-control" value="{{ $s->name }}" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Categoría *</label>
                        <select name="category" class="form-control" required>
                            <option value="corte" {{ $s->category == 'corte' ? 'selected' : '' }}>Corte</option>
                            <option value="barba" {{ $s->category == 'barba' ? 'selected' : '' }}>Barba</option>
                            <option value="combo" {{ $s->category == 'combo' ? 'selected' : '' }}>Combo</option>
                            <option value="otro" {{ $s->category == 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duración (min) *</label>
                        <input type="number" name="duration_min" class="form-control" value="{{ $s->duration_min }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Precio *</label>
                    <input type="number" name="price" step="0.01" class="form-control" value="{{ $s->price }}" required>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                    <input type="checkbox" name="is_active" id="service-active-{{ $s->id }}" {{ $s->is_active ? 'checked' : '' }} style="width:16px;height:16px;">
                    <label for="service-active-{{ $s->id }}" class="form-label" style="margin:0;">Activo</label>
                </div>
                <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('modal-service-edit-{{ $s->id }}')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
@endif

{{-- Modal: Nuevo Servicio --}}
<div class="modal-overlay" id="modal-service-new">
    <div class="modal">
        <div class="modal-title">📋 Agregar Servicio</div>
        <form method="POST" action="{{ route('admin.service.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-control" required placeholder="Ej: Corte Clásico...">
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
                <input type="number" name="price" step="0.01" class="form-control" required placeholder="5000">
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_active" id="service-active-new" checked style="width:16px;height:16px;">
                <label for="service-active-new" class="form-label" style="margin:0;">Activo</label>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-service-new')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection
